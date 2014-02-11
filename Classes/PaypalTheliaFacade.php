<?php
namespace Paypal\Classes;

use Symfony\Component\DependencyInjection\ContainerAware;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\AddressQuery;
use Thelia\Model\Order;
use Thelia\Tools\URL;

class PaypalTheliaFacade extends ContainerAware implements PaypalTheliaFacadeInterface {

    protected $order;

    public function __construct(Order $order) {
        $this->order=$order;
    }

    /**
     * Get Cart Total Amount
     *
     * @return float
     */
    public function getCartTotalAmount()
    {
        $session = $this->container->get('request')->getSession();
        return $session->getCart()->getTaxedAmount();
    }

    /**
     * Get Checkout Total Amount
     *
     * @return float
     */
    public function getTotalAmount()
    {
        //$this->order->get
        return (float)$this->order->getTotalAmount();
    }

    /**
     * Get Products Total Amount
     *
     * @return float
     */
    public function getProductsAmount()
    {
        // TODO: Implement getProductsAmount() method.
    }

    /**
     * Get Checkout Currency
     *
     * @return string
     */
    public function getCurrency()
    {
        $this->order->getCurrency()->getCode();
    }

    /**
     * Get site URL
     *
     * @return string
     */
    public function getUrlSite()
    {
        URL::getInstance()->absoluteUrl("/");
    }

    /**
     * Get Thelia cancel URL
     *
     * @return string
     */
    public function getCancelUrl()
    {
        // TODO: Implement getCancelUrl() method.
    }

    /**
     * Get IPN checkout URL
     *
     * @return string
     */
    public function getIpnCheckoutUrl()
    {
        // TODO: Implement getIpnCheckoutUrl() method.
    }

    /**
     * Get default shipping price (Express Shortcut)
     *
     * @return float
     */
    public function getDefaultShippingAmount()
    {
        // TODO: Implement getDefaultShippingAmount() method.
    }

    /**
     * Get payment type
     *
     * @param string $idCheckout Id Checkout
     *
     * @return string
     */
    public function getPaymentType($idCheckout = null)
    {
        // TODO: Implement getPaymentType() method.
    }

    /**
     * Get Checkout nb articles
     *
     * @return int
     */
    public function getNbArticles()
    {
        // TODO: Implement getNbArticles() method.
    }

    /**
     * Get Checkout Shipping Amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        // TODO: Implement getShippingAmount() method.
    }

    /**
     * Get Checkout Discount Amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        // TODO: Implement getDiscountAmount() method.
    }

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
    public function getProductsSoldFromDatabase()
    {
        // TODO: Implement getProductsSoldFromDatabase() method.
    }

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
    public function getProductsSoldFromSession()
    {
        // TODO: Implement getProductsSoldFromSession() method.
    }

    /**
     * Set Paypal transaction into session
     * SetExpressCheckout feeds it, DoExpressCheckoutPayment uses it
     *
     * @param string $payerId Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails
     * @param string $paymentAction How you want to obtain payment : Authorization|Order|Sale (PAYMENTREQUEST_n_PAYMENTACTION)
     * @param string $token The timestamped token value that was returned in the SetExpressCheckout
     * @param string $correlationId Identifies the API operation to PayPal and which must be provided to Merchant Technical Support if you need their assistance with a specific transaction
     * @param Commande $checkout Checkout for this transaction
     */
    public function setCurrentPaypalTransaction($payerId, $paymentAction, $token, $correlationId, Order $checkout = null)
    {
        new Session();
    }

    /**
     * Get Paypal transaction from session
     */
    public function getCurrentPaypalTransaction()
    {
        // TODO: Implement getCurrentPaypalTransaction() method.
    }

    /**
     * Return current checkout reference
     *
     * @return string checkout reference
     */
    public function getCheckoutReference()
    {
        // TODO: Implement getCheckoutReference() method.
    }

    /**
     * Get Paypal customization option
     *
     * @return array
     */
    public function getPaypalCustomizationOptions()
    {
        // TODO: Implement getPaypalCustomizationOptions() method.
    }

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
    public function getAddressDelivery()
    {
        $address = AddressQuery::create()
            ->findPk($this->order->getDeliveryOrderAddressId());
        return array(
            'name'=>$address->getLastname(),
            'street'=>$address->getAddress1(),
            'street2'=>$address->getAddress2(),
            'city'=>$address->getCity(),
            'state'=>'', // ????
            'zip'=>$address->getZipcode(),
            'countryCode'=>$address->getCountry()->getIsoalpha2()
        );
    }

    /**
     * Return array containing vendor info
     * $a['body_bg_color']
     * $a['logo_text']
     * $a['logo_image']
     *
     * @return array
     */
    public function getVendorInfoToCustomizePaypal()
    {
        // TODO: Implement getVendorInfoToCustomizePaypal() method.
    }

    /**
     * Check if the user is logged in or not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        // TODO: Implement isLoggedIn() method.
    }

}