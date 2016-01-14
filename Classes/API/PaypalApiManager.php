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

namespace Paypal\Classes\API;

use Paypal\Classes\PaypalResources;
use Paypal\Classes\vendor\MobileDetect\MobileDetect;
use Paypal\Paypal;

/**
 * Class PaypalApiManager
 * Assist in helping managing API
 */
class PaypalApiManager
{
    /** Live API */
    const DEFAULT_NVP_3T_API_URL_LIVE = 'https://api-3t.paypal.com/nvp';

    /** SandBox API */
    const DEFAULT_NVP_3T_API_URL_SANDBOX = 'https://api-3t.sandbox.paypal.com/nvp';

    /** API Version */
    const API_VERSION = '108.0';

    const PAYMENT_TYPE_ORDER = 'Order';
    const PAYMENT_TYPE_SALE = 'Sale';
    const PAYMENT_TYPE_AUTHORIZATION = 'Authorization';

    /** @var bool if SandBox mode is enabled or not */
    protected $isModeSandbox = true;

    protected $config=null;

    public function __construct()
    {
        $this->isModeSandbox = Paypal::isSandboxMode();
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
     * @param $data
     * @param \stdClass|null $ret
     * @param null $construct_scheme
     *
     * @return string
     */
    public static function arrayToNvp($data, \stdClass $ret = null, $construct_scheme = null)
    {
        if ($ret===null) {
            $ret = new \stdClass();
            $ret->value="";
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::arrayToNvp($value, $ret, $construct_scheme===null?$key:$construct_scheme."_".$key);
            }
        } else {
            $ret->value .= $construct_scheme."=".$data."&";
        }

        return substr($ret->value, 0, strlen($ret->value)-1);
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

        return $this->getPaypalUrl() .'?cmd=' . $cmd . '&token=' . $token;
    }

    /**
     * Return relevant PayPal redirect URL
     * According to SandBox Mode on or not
     *
     * @return string URL
     */
    public function getPaypalUrl()
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
        return number_format($number, 2, '.', '');
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
