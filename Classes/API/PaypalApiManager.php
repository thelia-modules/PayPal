<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/6/13
 * Time: 5:33 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes\API;

use Paypal\Model\ConfigInterface;
use Paypal\Classes\PaypalResources;
use Paypal\Classes\vendor\MobileDetect\MobileDetect;
/**
 * Class PaypalApiManager
 * Assist in helping managing API
 */
class PaypalApiManager
{
    /** Live API */
    CONST DEFAULT_NVP_3T_API_URL_LIVE = 'https://api-3t.paypal.com/nvp';

    /** SandBox API */
    CONST DEFAULT_NVP_3T_API_URL_SANDBOX = 'https://api-3t.sandbox.paypal.com/nvp';

    /** API Version */
    CONST API_VERSION = '108.0';

    CONST PAYMENT_TYPE_ORDER = 'Order';
    CONST PAYMENT_TYPE_SALE = 'Sale';
    CONST PAYMENT_TYPE_AUTHORIZATION = 'Authorization';

    /** @var bool if SandBox mode is enabled or not */
    protected $isModeSandbox = true;

    protected $config=null;

    /**
     * Constructor
     *
     * @param string $link Connection ex: $cnx = new Paypal(); $cnx->link;
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config=$config;
        $this->isModeSandbox = $this->checkIfSandBoxIsEnabled();
    }

    /**
     * Check in File if sandbox is enabled
     *
     * @return bool
     */
    public function checkIfSandBoxIsEnabled()
    {
        return $this->config->getSandbox();
    }

    /**
     * Get if SandBox is enabled or not
     *
     * @return bool
     */
    public function isModeSandbox()
    {
        return $this->isModeSandbox;
    }

    /**
     * Convert NVP string to array
     *
     * @param string $nvpstr NVP string
     *
     * @return array parameters
     */
    public static function nvpToArray($nvpstr)
    {
        $paypalResponse = array();
        parse_str($nvpstr, $paypalResponse);

        $cleanedArray = array();
        $previousKey = reset($paypalResponse);
        foreach ($paypalResponse as $key => $value) {
            if (1 === preg_match('#^([A-Z0-9_]+)$#', $key)) {
                $cleanedArray[$key] = $value;
                $previousKey = $key;
            } else {
                $cleanedArray[$previousKey] .= '&' . $key . '=' . $value;
            }
        }

        return $cleanedArray;
    }

    /**
     * Convert array to NVP string
     *
     * @param array $data parameters
     *
     * @return string NVP string
     */
    public static function arrayToNvp($data,\stdClass $ret=null, $construct_scheme=null)
    {
        if ($ret===null) {
            $ret = new \stdClass();
            $ret->value="";
        }
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
                self::arrayToNvp($value, $ret, $construct_scheme===null?$key:$construct_scheme."_".$key);
            }
        } else {
            $ret->value .= $construct_scheme."=".$data."&";
        }

        return substr($ret->value,0,strlen($ret->value)-1);
    }

    /**
     * Detect if user is using a mobile
     * Used in Express Checkout Mobile
     *
     * @return bool
     */
    public function isMobile()
    {
        $detect = new MobileDetect();

        return $detect->isMobile();
    }

    /**
     * Return Express checkout URL
     * Check itself if mobile or not
     *
     * @param string $token Paypal API token
     *
     * @return string
     */
    public function getExpressCheckoutUrl($token)
    {
        if ($this->isMobile()) {
            $cmd = PaypalResources::CMD_EXPRESS_CHECKOUT_MOBILE_KEY;
        } else {
            $cmd = PaypalResources::CMD_EXPRESS_CHECKOUT_KEY;
        }

        return $this->getPayPalUrl() .'?cmd=' . $cmd . '&token=' . $token;
    }

    /**
     * Return relevant PayPal redirect URL
     * According to SandBox Mode on or not
     *
     * @return string URL
     */
    public function getPayPalUrl()
    {
        $url = PaypalResources::PAYPAL_REDIRECT_SANDBOX_URL;
        if (!$this->isModeSandbox()) {
            $url = PaypalResources::PAYPAL_REDIRECT_NORMAL_URL;
        }

        return $url;
    }

    /**
     * Convert float into NVP number
     *
     * @param string $number number
     *
     * @return string
     */
    public static function convertFloatToNvpFormat($number)
    {
        return number_format(
            $number,
            2, '.', ''
        );
    }

    /**
     * Return API Url (sandbox or live)
     *
     * @return string
     */
    public function getApiUrl()
    {
        $url = self::DEFAULT_NVP_3T_API_URL_SANDBOX;
        if (!$this->isModeSandbox) {
            $url = self::DEFAULT_NVP_3T_API_URL_LIVE;
        }

        return $url;
    }
}
