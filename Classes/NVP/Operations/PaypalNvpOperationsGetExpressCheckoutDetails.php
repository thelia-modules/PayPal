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
 * Class GetExpressCheckoutDetails
 * Manage NVP GetExpressCheckoutDetails Operation
 */
class PaypalNvpOperationsGetExpressCheckoutDetails extends PaypalNvpOperationsBase
{
    /** @var string SetExpressCheckout API Token */
    protected $token = null;

    /**
     * Constructor
     *
     * @param PaypalApiCredentials $credentials API Credentials (3T)
     * @param string               $token       Token from SetExpressCheckout API
     */
    public function __construct(PaypalApiCredentials $credentials, $token)
    {
        $this->operationName = 'GetExpressCheckoutDetails';
        $this->credentials = $credentials;
        $this->token = $token;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();
        $request .= '&TOKEN=' . $this->token;

        return $request;
    }
}
