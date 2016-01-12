<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Paypal\Controller;

use Paypal\Classes\API\PaypalApiCredentials;
use Paypal\Classes\API\PaypalApiLogManager;
use Paypal\Classes\API\PaypalApiManager;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsDoExpressCheckoutPayment;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsGetExpressCheckoutDetails;
use Paypal\Classes\NVP\PaypalNvpMessageSender;
use Paypal\Paypal;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Model\Base\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
use Thelia\Module\BasePaymentModuleController;
use Thelia\Tools\URL;

/**
 * Class PaypalResponse
 * @package Paypal\Controller
 * @author Thelia <info@thelia.net>
 */
class PaypalResponse extends BasePaymentModuleController
{
    /** @var PaypalApiLogManager  */
    private $logger;

    public function __construct()
    {
        $this->logger = new PaypalApiLogManager();
    }

    /**
     * @param $order_id
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function ok($order_id)
    {
        $token = null;

        $message = '';

        try {
            $order = $this->checkorder($order_id, $token);
            /*
             * $payerid string value returned by paypal
             * $logger PaypalApiLogManager used to log transctions with paypal
             */
            $payerid = $this->getRequest()->get('PayerID');

            if (! empty($payerid)) {
                /*
                 * $config ConfigInterface Object that contains configuration
                 * $api PaypalApiCredentials Class used by the library to store and use 3T login(username, password, signature)
                 * $sandbox bool true if sandbox is enabled
                 */
                $api     = new PaypalApiCredentials();
                $sandbox = Paypal::isSandboxMode();
                /*
                 * Send getExpressCheckout & doExpressCheckout
                 * empty cart
                 */
                $getExpressCheckout = new PaypalNvpOperationsGetExpressCheckoutDetails(
                    $api,
                    $token
                );

                $request  = new PaypalNvpMessageSender($getExpressCheckout, $sandbox);
                $response = PaypalApiManager::nvpToArray($request->send());

                $this->logger->logTransaction($response);

                if (isset($response['ACK']) && $response['ACK'] === 'Success' &&
                    isset($response['PAYERID']) && $response['PAYERID'] === $payerid &&
                    isset($response['TOKEN']) && $response['TOKEN'] === $token
                ) {
                    $doExpressCheckout = new PaypalNvpOperationsDoExpressCheckoutPayment(
                        $api,
                        round($order->getTotalAmount(), 2),
                        $order->getCurrency()->getCode(),
                        $payerid,
                        PaypalApiManager::PAYMENT_TYPE_SALE,
                        $token,
                        URL::getInstance()->absoluteUrl("/module/paypal/listen")
                    );

                    $request  = new PaypalNvpMessageSender($doExpressCheckout, $token);
                    $response = PaypalApiManager::nvpToArray($request->send());

                    $this->logger->logTransaction($response);

                    // In case of pending status, log the reason to get usefull information (multi-currency problem, ...)
                    if (isset($response['ACK']) && $response['ACK'] === "Success" &&
                        isset($response['PAYMENTINFO_0_PAYMENTSTATUS']) && $response['PAYMENTINFO_0_PAYMENTSTATUS'] === "Pending") {
                        $this->getTranslator()->trans(
                            "Paypal transaction is pending. Reason: %reason",
                            [ 'reason' => $response['PAYMENTINFO_0_PENDINGREASON'] ],
                            Paypal::DOMAIN
                        );
                    }

                    /*
                     * In case of success, go to success page
                     * In case of error, show it
                     */
                    if (isset($response['ACK']) && $response['ACK'] === "Success"
                        && isset($response['PAYMENTINFO_0_PAYMENTSTATUS']) && $response['PAYMENTINFO_0_PAYMENTSTATUS'] === "Completed"
                        && isset($response['TOKEN']) && $response['TOKEN'] === $token
                    ) {
                        /*
                         * Set order status as paid
                         */
                        $event = new OrderEvent($order);
                        $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
                        $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

                        $this->redirectToSuccessPage($order_id);
                    } else {
                        $message = $this->getTranslator()->trans("Failed to validate your payment", [], Paypal::DOMAIN);
                    }
                } else {
                    $message = $this->getTranslator()->trans("Failed to validate payment parameters", [], Paypal::DOMAIN);
                }
            } else {
                $message = $this->getTranslator()->trans("Failed to find PayerID", [], Paypal::DOMAIN);
            }
        } catch (RedirectException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            $this->logger->getLogger()->error("Error occured while processing express checkout : " . $ex->getMessage());

            $message = $this->getTranslator()->trans(
                "Unexpected error: %mesg",
                [ '%mesg' => $ex->getMessage()],
                Paypal::DOMAIN
            );
        }

        $this->redirectToFailurePage($order_id, $message);
    }

    /*
     * @param $order_id int
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function cancel($order_id)
    {
        $token = null;

        try {
            $order = $this->checkorder($order_id, $token);

            $logger = new PaypalApiLogManager();
            $logger->getLogger()->warning("User canceled payment of order ".$order->getRef());

            $event = new OrderEvent($order);
            $event->setStatus(OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_CANCELED)->getId());
            $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

            $message = $this->getTranslator()->trans("You canceled your payment", [], Paypal::DOMAIN);
        } catch (\Exception $ex) {
            $this->logger->getLogger()->error("Error occured while canceling express checkout : " . $ex->getMessage());

            $message = $this->getTranslator()->trans(
                "Unexpected error: %mesg",
                [ '%mesg' => $ex->getMessage()],
                Paypal::DOMAIN
            );
        }

        $this->redirectToFailurePage($order_id, $message);
    }

    /*
     * @param $order_id int
     * @param &$token string|null
     * @throws \Exception
     * @return \Thelia\Model\Order
     */
    public function checkorder($order_id, &$token)
    {
        $token = $this->getRequest()->getSession()->get('Paypal.token');

        if ($token !== $this->getRequest()->get('token')) {
            throw new \Exception(
                $this->getTranslator()->trans(
                    "Invalid Paypal token. Please try again.",
                    [],
                    Paypal::DOMAIN
                )
            );
        }

        if (null === $order = OrderQuery::create()->findPk($order_id)) {
            throw new \Exception(
                $this->getTranslator()->trans(
                    "Invalid order ID. This order doesn't exists or doesn't belong to you.",
                    [],
                    Paypal::DOMAIN
                )
            );
        }

        return $order;
    }

    /**
     * Return a module identifier used to calculate the name of the log file,
     * and in the log messages.
     *
     * @return string the module code
     */
    protected function getModuleCode()
    {
        return "Paypal";
    }
}
