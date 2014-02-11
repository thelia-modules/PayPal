<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/14/13
 * Time: 4:16 PM
 * 
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes;

use Thelia\Model\Order;
/**
 * Class PaypalTheliaFacadeInterface
 * Retrieve data from a Thelia Platform and transmit it to the plugin
 */
interface PaypalTheliaFacadeInterface
{

    /**
     * Get Cart Total Amount
     *
     * @return float
     */
    public function getCartTotalAmount();

    /**
     * Get Checkout Total Amount
     *
     * @return float
     */
    public function getTotalAmount();

    /**
     * Get Products Total Amount
     *
     * @return float
     */
    public function getProductsAmount();

    /**
     * Get Checkout Currency
     *
     * @return string
     */
    public function getCurrency();

//    /**
//     * Get Thelia return URL
//     *
//     * @return string
//     */
//    public function getReturnUrl();

    /**
     * Get site URL
     *
     * @return string
     */
    public function getUrlSite();

    /**
     * Get Thelia cancel URL
     *
     * @return string
     */
    public function getCancelUrl();

    /**
     * Get IPN checkout URL
     *
     * @return string
     */
    public function getIpnCheckoutUrl();

    /**
     * Get default shipping price (Express Shortcut)
     *
     * @return float
     */
    public function getDefaultShippingAmount();

    /**
     * Get payment type
     *
     * @param string $idCheckout Id Checkout
     *
     * @return string
     */
    public function getPaymentType($idCheckout = null);

    /**
     * Get Checkout nb articles
     *
     * @return int
     */
    public function getNbArticles();

    /**
     * Get Checkout Shipping Amount
     *
     * @return float
     */
    public function getShippingAmount();

    /**
     * Get Checkout Discount Amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Get Product Sold list from database
     * Each object being sdtClass
     * Example :
     *     $products = array();
     *     $product1 = new stdClass();
     *     $product1->name = $item1Name;
     *     $product1->quantity = $item1Quantity;
     *     $product1->unitPrice = $item1Price;
     *     $product1->smallDescription = $item1MiniDesc;
     *     $products[] = $product1;
     *
     * @return array
     */
    public function getProductsSoldFromDatabase();

    /**
     * Get Product Sold list from session
     * Each object being sdtClass
     * Example :
     *     $products = array();
     *     $product1 = new stdClass();
     *     $product1->name = $item1Name;
     *     $product1->quantity = $item1Quantity;
     *     $product1->unitPrice = $item1Price;
     *     $product1->smallDescription = $item1MiniDesc;
     *     $products[] = $product1;
     *
     * @return array
     */
    public function getProductsSoldFromSession();

    /**
     * Set Paypal transaction into session
     * SetExpressCheckout feeds it, DoExpressCheckoutPayment uses it
     *
     * @param string   $payerId       Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails
     * @param string   $paymentAction How you want to obtain payment : Authorization|Order|Sale (PAYMENTREQUEST_n_PAYMENTACTION)
     * @param string   $token         The timestamped token value that was returned in the SetExpressCheckout
     * @param string   $correlationId Identifies the API operation to PayPal and which must be provided to Merchant Technical Support if you need their assistance with a specific transaction
     * @param Commande $checkout      Checkout for this transaction
     */
    public function setCurrentPaypalTransaction($payerId, $paymentAction, $token, $correlationId, Order $checkout = null);

    /**
     * Get Paypal transaction from session
     */
    public function getCurrentPaypalTransaction();

    /**
     * Return current checkout reference
     *
     * @return string checkout reference
     */
    public function getCheckoutReference();

    /**
     * Get Paypal customization option
     *
     * @return array
     */
    public function getPaypalCustomizationOptions();

    /**
     * Return array containing address delivery info
     * $a['name']
     * $a['street']
     * $a['street2']
     * $a['city']
     * $a['state']
     * $a['zip']
     * $a['countryCode']
     *
     * @return array
     */
    public function getAddressDelivery();

    /**
     * Return array containing vendor info
     * $a['body_bg_color']
     * $a['logo_text']
     * $a['logo_image']
     *
     * @return array
     */
    public function getVendorInfoToCustomizePaypal();

    /**
     * Check if the user is logged in or not
     *
     * @return bool
     */
    public function isLoggedIn();


}