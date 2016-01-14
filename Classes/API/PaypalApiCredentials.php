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

use Paypal\Paypal;
use Thelia\Core\Translation\Translator;

class PaypalApiCredentials
{

    /** @var string PayPal API username  */
    protected $apiUsername = null;

    /** @var string PayPal API password */
    protected $apiPassword = null;

    /** @var string PayPal API signature (Three Token Authentication) */
    protected $apiSignature = null;

    /**
     * Create a NVP Credentials
     *
     * @param string          $user      PayPal API username
     * @param string          $password  PayPal API password
     * @param string          $signature PayPal API signature (3T)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user = null, $password = null, $signature = null)
    {
        if ($user === null && $password === null && $signature === null) {
            $this->setDefaultCredentials();
        } else {
            if (empty($user) || empty($password) || empty($signature)) {
                throw new \InvalidArgumentException(
                    'PaypalApiCredentials : Missing Argument'
                );
            }
            $this->apiPassword = $password;
            $this->apiSignature = $signature;
            $this->apiUsername = $user;

        }
    }

    /**
     * Set credentials from database according to SandBox Mode
     *
     * @throws \InvalidArgumentException
     */
    protected function setDefaultCredentials()
    {
        $paypalApiManager = new PaypalApiManager();

        if ($paypalApiManager->isModeSandbox()) {
            $username  = Paypal::getConfigValue('sandbox_login', '');
            $password  = Paypal::getConfigValue('sandbox_password', '');
            $signature = Paypal::getConfigValue('sandbox_signature', '');
        } else {
            $username  = Paypal::getConfigValue('login', '');
            $password  = Paypal::getConfigValue('password', '');
            $signature = Paypal::getConfigValue('signature', '');
        }

        if (empty($username)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The username option must be set.'));
        }
        if (empty($password)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The password option must be set.'));
        }
        if (empty($signature)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The signature option must be set.'));
        }

        $this->apiUsername = $username;
        $this->apiPassword = $password;
        $this->apiSignature = $signature;
    }

    /**
     * Return API password
     *
     * @return string
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * Return API signature
     *
     * @return string
     */
    public function getApiSignature()
    {
        return $this->apiSignature;
    }

    /**
     * Return API username
     *
     * @return string
     */
    public function getApiUsername()
    {
        return $this->apiUsername;
    }
}
