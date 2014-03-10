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
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsSetExpressCheckout;
use Paypal\Classes\NVP\PaypalNvpMessageSender;

use Paypal\Model\PaypalConfig;
use Paypal\Paypal;

use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Base\CountryQuery;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderQuery;

/**
 * Class ConfigurePaypal
 * @package Paypal\Controller
 * @author Thelia <info@thelia.net>
 */
class GotoPaypal extends BaseFrontController
{
    /*
     * @param $order_id int
     * @return \Thelia\Core\HttpFoundation\Response
     *                                              Checks paypal.configure || paypal.configure.sandbox form and save config into json file
     */
    public function go($order_id)
    {
        /*
         * vars used for setExpressCheckout
         * $order Order The order object, which is used to get products and prices
         * $config ConfigInterface Object that contains configuration
         * $api PaypalApiCredentials Class used by the library to store and use 3T login(username, password, signature)
         * $redirect_api PaypalApiManager Instance of PaypalApiManager, only used to get checkout url ( and redirect to paypal )
         * $sandbox bool true if sandbox is enabled
         * $products array(array) 2D array that stores products in usable NVP format.
         * $i int counter
         * $logger PaypalApiLogManager used to log transactions with paypal
         */
        $order = OrderQuery::create()->findPk($order_id);
        $config = new PaypalConfig();
        $config->pushValues();
        $api = new PaypalApiCredentials($config);
        $redirect_api = new PaypalApiManager($config);
        $sandbox=$api->getConfig()->getSandbox();
        $products = array(array());
        $i=0;
        $logger = new PaypalApiLogManager();

        /*
         * Store products into 2d array $products
         */
        $products_amount = 0;
        foreach ($order->getOrderProducts() as $product) {
            if ($product !== null) {
                $amount = floatval($product->getWasInPromo()  ? $product->getPromoPrice():$product->getPrice());
                foreach ($product->getOrderProductTaxes() as $tax) {
                    $amount+= ($product->getWasInPromo() ? $tax->getPromoAmount():$tax->getAmount());
                }
                $products_amount+=$amount*$product->getQuantity();
                $products[0]["NAME".$i]=urlencode($product->getTitle());
                $products[0]["AMT".$i]=urlencode($amount);
                $products[0]["QTY".$i]=urlencode($product->getQuantity());
                $i++;
            }
        }

        /*
         * Compute difference between prodcts total and cart amount
         * -> get Coupons.
         */
        $delta = round($products_amount - $order->getTotalAmount($useless,false),2);
        if ($delta > 0) {
            $products[0]["NAME".$i]=Translator::getInstance()->trans("Discount");
            $products[0]["AMT".$i]=-$delta;
            $products[0]["QTY".$i]=1;
        }

        /*
         * Create setExpressCheckout request
         */
        $setExpressCheckout = new PaypalNvpOperationsSetExpressCheckout(
            $api,
            $order->getTotalAmount(),
            $order->getCurrency()->getCode(),
            Paypal::getPaypalURL('paiement', $order_id),
            Paypal::getPaypalURL('cancel', $order_id),
            0,
            array(
                "L_PAYMENTREQUEST"=>$products,
                "PAYMENTREQUEST"=>array(
                    array(
                        "SHIPPINGAMT"=>$order->getPostage(),
                        "ITEMAMT"=>$order->getTotalAmount($useless,false)
                    )
                )
            )
        );

        /*
         * Try to get customer's delivery address
         */
        $address= OrderAddressQuery::create()
            ->findPk($order->getDeliveryOrderAddressId());

        if ($address !== null) {
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
            $sender = new PaypalNvpMessageSender($setExpressCheckout,$sandbox);
            $response = $sender->send();
            $logger->logTransaction($response);
            $response = PaypalApiManager::nvpToArray($response);
            /*
             * if setExpressCheckout is correct, store values in the session & redirect to paypal checkout page
             * else print error. ( return $this->render ... )
             */
            if (isset($response['ACK']) && $response['ACK'] === "Success" &&
                isset($response['TOKEN']) && !empty($response['TOKEN'])) {
                $sess = $this->getRequest()->getSession();
                $sess->set("Paypal.token",$response['TOKEN']);
                $this->redirect($redirect_api->getExpressCheckoutUrl($response['TOKEN']));
            }
        }

        return $this->render("gotopaypalfail",array(), 500);
    }
}
