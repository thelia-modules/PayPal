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

namespace Paypal\Classes\NVP;

use Paypal\Classes\NVP\Operations\PaypalNvpOperationInterface;
use Paypal\Classes\API\PaypalApiManager;

/**
 * Class PaypalNvpMessageSender
 *
 * Send NVP requests via Curl
 *
 * Example for the API SetExpressCheckout call on the SandBox:
 * $paypal = new Paypal();
 * $nvpSetExpressCheckout = new PaypalNvpOperationsSetExpressCheckout(
 *     new PaypalApiCredentials(new PayPalVariableRepository($paypal->link)),
 *     $amount,
 *     $currencyID,
 *     $return_url,
 *     $cancel_url,
 * );
 * $nvpMessageSender = new PaypalNvpMessageSender($nvpSetExpressCheckout, true);
 * $response = $nvpMessageSender->send();
 */
class PaypalNvpMessageSender
{
    /** @var string message to send */
    protected $message = null;

    /** @var bool if sandbox mode is enabled */
    protected $isSandbox = true;

    /**
     * Constructor
     *
     * @param PaypalNvpOperationInterface $nvpMessage NVP message to send
     * @param bool                        $isSandbox  if sandbox mode enabled
     */
    public function __construct(PaypalNvpOperationInterface $nvpMessage, $isSandbox = true)
    {
        $this->isSandbox = $isSandbox;
        $this->message = $nvpMessage->getRequest();
    }

    /**
     * Send request via Curl
     *
     * @return string APÏ response
     */
    public function send()
    {
        $paypalApiManager = new PaypalApiManager();

        $url = $paypalApiManager->getApiUrl() . '?' . $this->message;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        return $response;
    }
}
