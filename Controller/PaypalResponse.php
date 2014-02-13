<?php
/**
 * Created by PhpStorm.
 * User: benjamin
 * Date: 11/02/14
 * Time: 10:52
 */

namespace Paypal\Controller;

use Paypal\Classes\NVP\Operations\PaypalNvpOperationsDoExpressCheckoutPayment;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsGetExpressCheckoutDetails;
use Paypal\Classes\API\PaypalApiCredentials;
use Paypal\Classes\API\PaypalApiManager;
use Paypal\Classes\NVP\PaypalNvpMessageSender;

use Paypal\Model\PaypalConfig;
use Paypal\Paypal;

use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Base\OrderQuery;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Tools\URL;
use Paypal\Classes\API\PaypalApiLogManager;

class PaypalResponse extends BaseFrontController {

    /**
     * @param $order_id
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function ok($order_id) {
        /*
         * Check if token&order are valid
         */
        $token = null;
        $order = $this->checkorder($order_id,$token);
        /*
         * $payerid string value returned by paypal
         * $logger PaypalApiLogManager used to log transctions with paypal
         */
        $payerid=$this->getRequest()->get('PayerID');
        $logger = new PaypalApiLogManager();

        if(!empty($payerid)) {
            /*
             * $config ConfigInterface Object that contains configuration
             * $api PaypalApiCredentials Class used by the library to store and use 3T login(username, password, signature)
             * $sandbox bool true if sandbox is enabled
             */
            $config = new PaypalConfig();
            $config->pushValues();
            $api = new PaypalApiCredentials($config);
            $sandbox=$api->getConfig()->getSandbox();
            /*
             * Send getExpressCheckout & doExpressCheckout
             * empty cart
             */
            $getExpressCheckout = new PaypalNvpOperationsGetExpressCheckoutDetails(
                $api,
                $token
            );
            $request = new PaypalNvpMessageSender($getExpressCheckout, $sandbox);
            $response = $request->send();
            $logger->logTransaction($response);
            $response = PaypalApiManager::nvpToArray($response);

            if (isset($response['ACK']) && $response['ACK'] === 'Success' &&
                isset($response['PAYERID']) && $response['PAYERID'] === $payerid &&
                isset($response['PAYERSTATUS']) && $response['PAYERSTATUS'] === 'verified' &&
                isset($response['TOKEN']) && $response['TOKEN'] === $token) {

                $doExpressCheckout = new PaypalNvpOperationsDoExpressCheckoutPayment(
                    $api,
                    $order->getTotalAmount(),
                    $order->getCurrency()->getCode(),
                    $payerid,
                    PaypalApiManager::PAYMENT_TYPE_SALE,
                    $token,
                    URL::getInstance()->absoluteUrl("/module/paypal/listen")
                );
                $request = new PaypalNvpMessageSender($doExpressCheckout, $token);
                $response = $request->send();
                $logger->logTransaction($response);
                $response = PaypalApiManager::nvpToArray($response);

                /*
                 * In case of success, go to success page
                 * In case of error, show it
                 */
                if (isset($response['ACK']) && $response['ACK'] === "Success" &&
                    isset($response['PAYMENTINFO_0_PAYMENTSTATUS']) && $response['PAYMENTINFO_0_PAYMENTSTATUS'] === "Completed" &&
                    isset($response['TOKEN']) && $response['TOKEN'] === $token) {

                    /*
                     * Set order status as paid
                     */
                    $event = new OrderEvent($order);
                    $event->setStatus(Paypal::STATUS_PAID);
                    $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS,$event);

                    $this->redirect(
                        URL::getInstance()->absoluteUrl(
                            $this->getRoute(
                                "order.placed",
                                array(
                                    "order_id"=>$order_id
                                )
                            )
                        )
                    );
                }
            }

        }
        /*
         * If no redirection done ( === error ): Empty cart
         */
        $cart_event = new CartEvent($this->getRequest()->getSession()->getCart());
        $this->dispatch(TheliaEvents::CART_CLEAR, $cart_event);

        return $this->render("gotopaypalfail");
    }

    /*
     * @param $order_id int
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function cancel($order_id) {
        /*
         * Check if token&order are valid
         */
        $token=null;
        $order = $this->checkorder($order_id,$token);
        /*
         * $logger PaypalApiLogManager used to log transctions with paypal
         */
        $logger = new PaypalApiLogManager('canceled_orders');
        $logger->logText("Order canceled: ".$order->getRef());
        /*
         * Empty cart and update order status to canceled by using the event
         */
        $cart_event = new CartEvent($this->getRequest()->getSession()->getCart());
        $this->dispatch(TheliaEvents::CART_CLEAR, $cart_event);

        $event = new OrderEvent($order);
        $event->setStatus(Paypal::STATUS_CANCELED);
        $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS,$event);

        return $this->render("ordercanceled", array("order_ref"=>$order->getRef()));
    }

    /*
     * @param $order_id int
     * @param &$token string|null
     * @throws \Exception
     * @return \Thelia\Model\Order
     */
    public function checkorder($order_id, &$token) {
        $token = $this->getRequest()->getSession()->get('Paypal.token');
        if($token !== $this->getRequest()->get('token')) {
            throw new \Exception("The token is not valid.");
        }

        $customer_id = $this->getRequest()->getSession()->getCustomerUser()->getId();
        $order =OrderQuery::create()
            ->filterByCustomerId($customer_id)
            ->findPk($order_id);
        if($order === null) {
            throw new \Exception("The order id is not valid. This order doesn't exists or doesn't belong to you.");
        }

        return $order;
    }
} 