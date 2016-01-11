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

namespace Paypal\Classes\NVP\Operations;

use Paypal\Classes\API\PaypalApiCredentials;

/**
 * Class PaypalNvpOperationsDoExpressCheckoutPayment
 * Manage NVP DoExpressCheckoutPayment Operation
 */
class PaypalNvpOperationsDoExpressCheckoutPayment extends PaypalNvpOperationsBase
{
    /** @var string Payer ID returned by PayPal when it redirects the buyer's browser to your site */
    protected $payerId = null;

    /** @var string SetExpressCheckout API Token */
    protected $token = null;

    /** @var string Transaction amount
     * Must be specified as 2000.00 or 2,000.00.
     * The specified amount cannot exceed USD $10,000.00, regardless of the currency used.
     */
    protected $amount = null;

    /** @var string Currency id ex: EUR */
    protected $currencyId = null;

    /** @var string Payment action ex: sale/order */
    protected $paymentAction = null;

    /** @var string URL IPN listener */
    protected $ipnListenerUrl = null;
    /** @var string Thelia_Cart_ECM|Thelia_Cart_ECS */
    //protected $buttonSource;

    /**
     * Constructor
     *
     * @param PaypalApiCredentials $credentials API Credentials (3T)
     * @param string $amount Transaction amount. Must be specified as 2000.00 or 2,000.00. The specified amount cannot exceed USD $10,000.00, regardless of the currency used.
     * @param string $currencyId Currency id ex: EUR
     * @param string $payerId Payer ID returned by PayPal when it redirects the buyer's browser to your site
     * @param string $paymentAction Payment action ex: sale/order
     * @param string $token Token returned by PayPal SetExpressCheckout API when it redirects the buyer's browser to your site.
     * @param string $ipnListenerUrl Url Paypal will call in order to confirm payment
     * @param $buttonSource
     */
    public function __construct(
        PaypalApiCredentials $credentials,
        $amount,
        $currencyId,
        $payerId,
        $paymentAction,
        $token,
        $ipnListenerUrl
        //$buttonSource
    ) {
        $this->operationName  = 'DoExpressCheckoutPayment';
        $this->token          = $token;
        $this->amount         = $amount;
        $this->payerId        = $payerId;
        $this->credentials    = $credentials;
        $this->currencyId     = $currencyId;
        $this->paymentAction  = $paymentAction;
        $this->ipnListenerUrl = $ipnListenerUrl;
        //$this->buttonSource = $buttonSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        $request = parent::getRequest();
        $request .= '&TOKEN=' . $this->token;
        $request .= '&PAYERID=' . $this->payerId;
        $request .= '&PAYMENTREQUEST_0_AMT=' . $this->amount;
        $request .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . $this->currencyId;
        $request .= '&PAYMENTREQUEST_0_PAYMENTACTION=' . $this->paymentAction;

        //$request .='&BUTTONSOURCE=' . $this->buttonSource;
        return $request;
    }
}
