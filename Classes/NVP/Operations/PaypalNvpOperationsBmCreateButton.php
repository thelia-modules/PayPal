<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/5/13
 * Time: 5:36 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;
use Paypal\Classes\API\PaypalApiCredentials;
use Paypal\Classes\API\PaypalApiManager;
/**
 * Class NvpBmCreateButton
 * Manage NVP BMCreateButtonOperation Operation
 */
class PaypalNvpOperationsBmCreateButton extends PaypalNvpOperationsBase
{

    CONST BUTTON_CODE_HOSTED =    'HOSTED';
    CONST BUTTON_CODE_ENCRYPTED = 'ENCRYPTED';
    CONST BUTTON_CODE_CLEARTEXT = 'CLEARTEXT';
    CONST BUTTON_CODE_TOKEN =     'TOKEN';


    CONST BUTTON_TYPE_BUYNOW =          'BUYNOW';
    CONST BUTTON_TYPE_CART =            'CART';
    CONST BUTTON_TYPE_GIFTCERTIFICATE = 'GIFTCERTIFICATE';
    CONST BUTTON_TYPE_SUBSCRIBE =       'SUBSCRIBE';
    CONST BUTTON_TYPE_DONATE =          'DONATE';
    CONST BUTTON_TYPE_UNSUBSCRIBE =     'UNSUBSCRIBE';
    CONST BUTTON_TYPE_VIEWCART =        'VIEWCART';
    CONST BUTTON_TYPE_PAYMENTPLAN =     'PAYMENTPLAN';
    CONST BUTTON_TYPE_AUTOBILLING =     'AUTOBILLING';
    CONST BUTTON_TYPE_PAYMENT =         'PAYMENT';

    /** @var string The kind of button code to create */
    protected $buttonType = null;

    /** @var string The kind of button you want to create. */
    protected $buttonCode = null;

    /** @var array Vendor Info to be sent to PayPal */
    protected $vendorInfo = array();

    /** @var array Customer Info to be sent to PayPal */
    protected $customerInfo = array();

    /** @var array Cart Info to be sent to PayPal */
    protected $cartInfo = array();

    /** @var array Billing Info to be sent to PayPal */
    protected $billingInfo = array();

    /** @var array Process Info to be sent to PayPal */
    protected $processInfo = array();


    /** @var bool If Paypal has to use Thelia Customer Address */
    protected $isPaypalAddressOverrided = true;

    /**
     * Constructor
     * Payload example :
     * array_merge(
     *     $encryptedButton->setProcessInfo(xxx),
     *     $encryptedButton->setBillingInfo(xxx),
     *     $encryptedButton->setCartInfo(xxx),
     *     $encryptedButton->setCustomerInfo(xxx),
     *     $encryptedButton->setVendorInfo(xxx)
     *  );
     *
     * @param PaypalApiCredentials $credentials API Credentials (3T)
     * @param string               $buttonCode  HOSTED|ENCRYPTED|CLEARTEXT|TOKEN
     * @param string               $buttonType  BUYNOW|CART|GIFTCERTIFICATE
     *                                          |SUBSCRIBE|DONATE|UNSUBSCRIBE
     *                                          |VIEWCART|PAYMENTPLAN|AUTOBILLING
     *                                          |PAYMENT
     * @param array                $payload     Operation extra args
     */
    public function __construct(
        PaypalApiCredentials $credentials = null,
        $buttonCode = self::BUTTON_CODE_ENCRYPTED,
        $buttonType = self::BUTTON_TYPE_BUYNOW,
        array $payload = null
    )
    {
        $this->operationName = 'BMCreateButton';

        if ($credentials ===null) {
            $this->credentials = new PaypalApiCredentials(
                new PayPalVariableRepository()
            );
        } else {
            $this->credentials = $credentials;
        }

        $this->buttonCode = $buttonCode;
        $this->buttonType = $buttonType;
        $this->payload = $payload;

        if (null === $this->payload) {
            $this->payload = array();
        }
    }

    /**
     * Set Cart Info to be displayed on PayPal page
     * The values you pass must not contain any of these special characters (){}<>\";
     *
     * @param float $subtotal Amount charged for the transaction. If shipping, handling, and taxes are not specified, this is the total amount charged. Ex: 12.00
     * @param float $tax      Taxes charged. This amount is added to subtotal for the total amount. Ex: 12.00
     * @param float $shipping Shipping charged. This amount is added to subtotal for the total amount. Ex: 12.00
     * @param float $handling Handling charged. This amount is added to subtotal for the total amount. Ex: 12.00
     */
    public function setCartInfo($subtotal, $tax = null, $shipping = null, $handling = null)
    {
        $this->cartInfo = array(
            'subtotal' => urlencode(PaypalApiManager::convertFloatToNvpFormat($subtotal)),
            'tax' => urlencode(PaypalApiManager::convertFloatToNvpFormat($tax)),
            'shipping' => urlencode(PaypalApiManager::convertFloatToNvpFormat($shipping)),
            'handling' => urlencode(PaypalApiManager::convertFloatToNvpFormat($handling))
        );
        $this->payload = array_merge($this->payload, $this->cartInfo);
    }

    /**
     * Set Process Info to be displayed on PayPal page
     * The values you pass must not contain any of these special characters (){}<>\";
     *
     * @param string $notifyUrl       The URL to which PayPal posts information about the transaction in the form of Instant Payment Notification. Be sure to enter the complete URL, including http:// or https://.
     * @param string $cancelReturn    The browser will be redirected to this URL if the buyer clicks “Return to Merchant” link. Be sure to enter the complete URL, including http:// or https://.
     * @param string $return          The URL to which the buyer’s browser is redirected to after completing the payment. Be sure to enter the complete URL, including http:// or https://.
     * @param string $paymentAction   Indicates whether the transaction is for payment on a final sale or an authorisation for a final sale (to be captured later). Allowed authorization|sale
     * @param string $currencyCode    The currency of the payment. The default is USD.
     * @param string $addressOverride The payer is shown the passed-in address but cannot edit it. This variable is overridden if there are errors in the address. The allowable values are true/false. Default is false.
     *
     * @return $this
     */
    public function setProcessInfo(
        $notifyUrl = null,
        $cancelReturn = null,
        $return = null,
        $paymentAction = 'sale',
        $currencyCode = null,
        $addressOverride = null
    )
    {
        $this->processInfo = array(
//            'notify_url' => $notifyUrl,
            'cancel_return' => $cancelReturn,
//            'return' => $return,
            'paymentaction' => strtolower($paymentAction),
            'currency_code' => $currencyCode,
            'address_override' => $addressOverride
        );
        if (null === $this->payload) {
            $this->payload = array();
        }
        $this->payload = array_merge($this->payload, $this->processInfo);

        return $this;
    }

    /**
     * Set Vendor Info to be displayed on PayPal page
     * The values you pass must not contain any of these special characters (){}<>\";
     *
     * @param string $bodyBgColor Color of the surrounding background of the payment page. Ex : AEAEAE
     * @param string $logoImage   Image displayed in the logo. The acceptable file extension formats are .gif, .jpg, .jpeg, or .png. The width of the image cannot be more than 940 pixels.
     * @param string $logoText    Business name displayed on your profile page. This field is editable and text specified here is displayed on the header if logoImage is not specified.
     *
     * @return $this
     */
    public function setVendorInfo($bodyBgColor = null, $logoImage = null, $logoText = null)
    {
        $this->vendorInfo = array(

            'cpp_logo_image' => $logoImage,
            'cpp_cart_border_color' => $bodyBgColor,
        );
        if (null === $this->payload) {
            $this->payload = array();
        }
        $this->payload = array_merge($this->payload, $this->vendorInfo);

        return $this;
    }

    /**
     * Set Billing Info to be displayed on PayPal page
     * The values you pass must not contain any of these special characters (){}<>\";
     *
     * @param string $billing_first_name First name of person the item is being billed to.
     * @param string $billing_last_name Last name of person the item is being billed to.
     * @param string $billing_address1 Street name of the billing address. (1 of 2 fields).
     * @param string $billing_address2 Street name of the billing address. (2 of 2 fields).
     * @param string $billing_city City name of the billing address.
     * @param string $billing_state State name of the billing address.
     * @param string $billing_zip Zip code of the billing address.
     * @param string $billing_country Country code of the billing address.
     */
    public function setBillingInfo(
        $billing_first_name = null,
        $billing_last_name = null,
        $billing_address1 = null,
        $billing_address2 = null,
        $billing_city = null,
        $billing_state = null,
        $billing_zip = null,
        $billing_country = null
    ) {
        $this->billingInfo = array(
            'billing_first_name' => $billing_first_name,
            'billing_last_name' => $billing_last_name,
            'billing_address1' => $billing_address1,
            'billing_address2' => $billing_address2,
            'billing_city' => $billing_city,
            'billing_state' => $billing_state,
            'billing_zip' => $billing_zip,
            'billing_country' => $billing_country,
//            'invoice' => urlencode($invoice),
//            'bn' => urlencode($bn),
//            'custom' => urlencode($custom),
        );
        if (null === $this->payload) {
            $this->payload = array();
        }
        $this->payload = array_merge($this->payload, $this->billingInfo);
    }

    public function setFromSession()
    {

    }

    /**
     * Set customer delivery address
     *
     * @param string $firstname   First Name
     * @param string $lastname    Last Name
     * @param string $street      Street
     * @param string $street2     Street 2
     * @param string $city        City
     * @param string $state       State
     * @param string $zip         Zip
     * @param string $countryCode CountryCode FR|US|UK
     *
     * @return $this
     */
    public function setCustomerDeliveryAddress($firstname, $lastname, $street, $street2, $city, $state, $zip, $countryCode)
    {
        $this->isPaypalAddressOverrided = true;

        $this->customerInfo = array(
            'first_name' => $firstname,
            'last_name' => $lastname,
            'address1' => $street,
            'address2' => $street2,
            'state' => $state,
            'zip' => $zip,
            'city' => $city,
            'country' => $countryCode,
        );
        if (null === $this->payload) {
            $this->payload = array();
        }
        $this->payload = array_merge($this->payload, $this->customerInfo);

        return $this;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();
        $request .= '&BUTTONCODE=' . urlencode($this->buttonCode);
        $request .= '&BUTTONTYPE=' . urlencode($this->buttonType);
        $request .= '&RETURNURL=' . 'http%3A%2F%2F192.168.56.101%3Ffond%3Dpaypal_confirmation_payment';
        $request .= '&BN=Thelia_Cart_HSS';

        $i = 0;
        if (isset($this)) {
            foreach ($this->payload as $key => $value) {
                $request .= '&L_BUTTONVAR' . $i . '=' . urlencode($key) . '=' . urlencode($value);
                $i++;
            }
        }

        return $request;
    }

    /**
     * Return current payload
     *
     * @return array current payload
     */
    public function getPayload()
    {
        return $this->payload;
    }


}
