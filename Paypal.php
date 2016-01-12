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

namespace Paypal;

use Paypal\Classes\API\PaypalApiCredentials;
use Paypal\Classes\API\PaypalApiLogManager;
use Paypal\Classes\API\PaypalApiManager;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsSetExpressCheckout;
use Paypal\Classes\NVP\PaypalNvpMessageSender;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\CountryQuery;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderQuery;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Tools\URL;

/**
 * Class Paypal
 * @package Paypal
 * @author Thelia <info@thelia.net>
 */
class Paypal extends AbstractPaymentModule
{
    const DOMAIN = 'paypal';

    /**
     * The confirmation message identifier
     */
    const CONFIRMATION_MESSAGE_NAME = 'paypal_payment_confirmation';

    public function pay(Order $order)
    {
        $orderId = $order->getId();

        /** @var Router $router */
        $router = $this->getContainer()->get('router.paypal');

        $successUrl = URL::getInstance()->absoluteUrl(
            $router->generate('paypal.ok', ['order_id' => $order->getId()])
        );

        $cancelUrl = URL::getInstance()->absoluteUrl(
            $router->generate('paypal.cancel', ['order_id' => $order->getId()])
        );

        $order = OrderQuery::create()->findPk($orderId);

        $api          = new PaypalApiCredentials();
        $redirect_api = new PaypalApiManager();
        $products     = array(array());
        $itemIndex    = 0;
        $logger       = new PaypalApiLogManager();

        /*
         * Store products into 2d array $products
         */
        $products_amount = 0;

        foreach ($order->getOrderProducts() as $product) {
            if ($product !== null) {
                $amount = floatval($product->getWasInPromo() ? $product->getPromoPrice() : $product->getPrice());
                foreach ($product->getOrderProductTaxes() as $tax) {
                    $amount += $product->getWasInPromo() ? $tax->getPromoAmount() : $tax->getAmount();
                }
                $products_amount += $amount * $product->getQuantity();
                $products[0][ "NAME" . $itemIndex ] = urlencode($product->getTitle());
                $products[0][ "AMT" . $itemIndex ]  = urlencode(round($amount, 2));
                $products[0][ "QTY" . $itemIndex ]  = urlencode($product->getQuantity());
                $itemIndex ++;
            }
        }

        /*
         * Compute difference between prodcts total and cart amount
         * -> get Coupons.
         */
        $delta = round($products_amount - $order->getTotalAmount($useless, false), 2);

        if ($delta > 0) {
            $products[0][ "NAME" . $itemIndex ] = Translator::getInstance()->trans("Discount");
            $products[0][ "AMT" . $itemIndex ]  = - $delta;
            $products[0][ "QTY" . $itemIndex ]  = 1;
        }

        /*
         * Create setExpressCheckout request
         */
        $setExpressCheckout = new PaypalNvpOperationsSetExpressCheckout(
            $api,
            round($order->getTotalAmount(), 2),
            $order->getCurrency()->getCode(),
            $successUrl,
            $cancelUrl,
            0,
            array(
                "L_PAYMENTREQUEST" => $products,
                "PAYMENTREQUEST"   => array(
                    array(
                        "SHIPPINGAMT" => round($order->getPostage(), 2),
                        "ITEMAMT"     => round($order->getTotalAmount($useless, false), 2)
                    )
                )
            )
        );

        /*
         * Try to get customer's delivery address
         */
        if (null !== $address = OrderAddressQuery::create()->findPk($order->getDeliveryOrderAddressId())) {
            /*
             * If address is found, set address in setExpressCheckout request
             */
            $setExpressCheckout->setCustomerDeliveryAddress(
                $address->getLastname(),
                $address->getAddress1(),
                $address->getAddress2(),
                $address->getCity(),
                "", // State
                $address->getZipcode(),
                CountryQuery::create()->findPk($address->getCountryId())->getIsoalpha2()
            );

            /*
             * $sender PaypalNvpMessageSender Instance of the class that sends requests
             * $response string NVP response of paypal for setExpressCheckout request
             * $req array array cast of NVP response
             */
            $sender = new PaypalNvpMessageSender($setExpressCheckout, self::isSandboxMode());

            $response = $sender->send();

            if ($response) {
                $responseData = PaypalApiManager::nvpToArray($response);

                $logger->logTransaction($responseData);
                /*
                 * if setExpressCheckout is correct, store values in the session & redirect to paypal checkout page
                 * else print error. ( return $this->render ... )
                 */
                if (isset($responseData['ACK']) && $responseData['ACK'] === "Success"
                    &&
                    isset($responseData['TOKEN']) && ! empty($responseData['TOKEN'])
                ) {
                    $sess = $this->getRequest()->getSession();
                    $sess->set("Paypal.token", $responseData['TOKEN']);

                    return new RedirectResponse(
                        $redirect_api->getExpressCheckoutUrl($responseData['TOKEN'])
                    );
                }
            } else {
                $logger->getLogger()->error(
                    Translator::getInstance()->trans(
                        "Failed to get a valid Paypal response. Please try again",
                        [],
                        self::DOMAIN
                    )
                );
            }
        } else {
            $logger->getLogger()->error(
                Translator::getInstance()->trans(
                    "Failed to get customer delivery address",
                    [],
                    self::DOMAIN
                )
            );
        }

        // Failure !
        return new RedirectResponse(
            $this->getPaymentFailurePageUrl(
                $orderId,
                // Pas de point final, sinon 404 !
                Translator::getInstance()->trans(
                    "Sorry, something did not worked with Paypal. Please try again, or use another payment type",
                    [],
                    self::DOMAIN
                )
            )
        );
    }

    public function isValidPayment()
    {
        $valid = false;

        // Check if total order amount is within the module's limits
        $order_total = $this->getCurrentOrderTotalAmount();

        $min_amount = Paypal::getConfigValue('minimum_amount', 0);
        $max_amount = Paypal::getConfigValue('maximum_amount', 0);

        if (
            ($order_total > 0)
            &&
            ($min_amount <= 0 || $order_total >= $min_amount)
            &&
            ($max_amount <= 0 || $order_total <= $max_amount)
        ) {
            // Check cart item count
            $cartItemCount = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher())->countCartItems();

            if ($cartItemCount <= Paypal::getConfigValue('cart_item_count', 9)) {
                $valid = true;

                if (Paypal::isSandboxMode()) {
                    // In sandbox mode, check the current IP
                    $raw_ips = explode("\n", Paypal::getConfigValue('allowed_ip_list', ''));

                    $allowed_client_ips = array();

                    foreach ($raw_ips as $ip) {
                        $allowed_client_ips[] = trim($ip);
                    }

                    $client_ip = $this->getRequest()->getClientIp();

                    $valid = in_array($client_ip, $allowed_client_ips);
                }
            }
        }

        return $valid;
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        // Setup some default values at first install
        if (null === self::getConfigValue('minimum_amount', null)) {
            self::setConfigValue('minimum_amount', 0);
            self::setConfigValue('maximum_amount', 0);
            self::setConfigValue('send_payment_confirmation_message', 1);
        }

        if (null === MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)) {
            $message = new Message();

            $message
                ->setName(self::CONFIRMATION_MESSAGE_NAME)
                ->setHtmlTemplateFileName('paypal-payment-confirmation.html')
                ->setTextTemplateFileName('paypal-payment-confirmation.txt')
                ->setLocale('en_US')
                ->setTitle('Paypal payment confirmation')
                ->setSubject('Payment of order {$order_ref}')
                ->setLocale('fr_FR')
                ->setTitle('Confirmation de paiement par Paypal')
                ->setSubject('Confirmation du paiement de votre commande {$order_ref}')
                ->save()
            ;
        }

        /* Deploy the module's image */
        $module = $this->getModuleModel();

        if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null)
    {
        if (null === self::getConfigValue('login', null)) {
            $database = new Database($con);

            $statement = $database->execute('select * from paypal_config');

            while ($statement && $config = $statement->fetchObject()) {
                switch($config->name) {
                    case 'login_sandbox':
                        Paypal::setConfigValue('sandbox_login', $config->value);
                        break;

                    case 'password_sandbox':
                        Paypal::setConfigValue('sandbox_password', $config->value);
                        break;

                    case 'signature_sandbox':
                        Paypal::setConfigValue('sandbox_signature', $config->value);
                        break;

                    default:
                        Paypal::setConfigValue($config->name, $config->value);
                        break;
                }
            }
        }

        parent::update($currentVersion, $newVersion, $con);
    }

    public static function isSandboxMode()
    {
        return 1 == intval(self::getConfigValue('sandbox'));
    }

    public function destroy(ConnectionInterface $con = null, $deleteModuleData = false)
    {
        if ($deleteModuleData) {
            MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)->delete();
        }
    }

    /**
     * if you want, you can manage stock in your module instead of order process.
     * Return false to decrease the stock when order status switch to pay
     *
     * @return bool
     */
    public function manageStockOnCreation()
    {
        return false;
    }
}
