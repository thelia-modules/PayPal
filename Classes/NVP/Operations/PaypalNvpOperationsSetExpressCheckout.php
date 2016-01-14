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
use Paypal\Classes\API\PaypalApiManager;

/**
 * Class PaypalNvpOperationsSetExpressCheckout
 * Manage NVP SetExpressCheckout Operation
 */
class PaypalNvpOperationsSetExpressCheckout extends PaypalNvpOperationsBase
{
    /** @var string Transaction amount
     * Must be specified as 2000.00 or 2,000.00.
     * The specified amount cannot exceed USD $10,000.00, regardless of the currency used.
     */
    protected $amount = null;

    /** @var string Currency id ex: EUR */
    protected $currencyId = null;

    /** @var string URL when operation is successful */
    protected $returnUrl = null;

    /** @var string URL when operation is cancelled */
    protected $cancelUrl = null;

    /** @var string allowing the shortcut transaction */
    protected $billingAgreement = null;

    /** @var bool If Paypal has to use Thelia Customer Address */
    protected $isPaypalAddressOverrided = false;

    /** @var string Delivery Address  */
    protected $name = null;
    /** @var string Delivery Address  */
    protected $street = null;
    /** @var string Delivery Address  */
    protected $street2 = null;
    /** @var string Delivery Address  */
    protected $city = null;
    /** @var string Delivery Address  */
    protected $state = null;
    /** @var string Delivery Address  */
    protected $zip = null;
    /** @var string Delivery Address  */
    protected $countryCode = null;

    /**
     * Constructor
     *
     * @param PaypalApiCredentials $credentials      API Credentials (3T)
     * @param string               $amount           Transaction amount (<USD $10,000.00)
     * @param string               $currencyId       Currency id ex: EUR
     * @param string               $returnUrl        URL when operation is successful
     * @param string               $cancelUrl        URL when operation is cancelled
     * @param int                  $billingAgreement Billing agreement allowing reference transaction
     * @param array                $payload          Operation extra args
     */
    public function __construct(
        PaypalApiCredentials $credentials,
        $amount,
        $currencyId,
        $returnUrl,
        $cancelUrl,
        $billingAgreement = 0,
        array $payload = null
    ) {
        $this->operationName = 'SetExpressCheckout';
        $this->credentials = $credentials;

        $this->amount = $amount;
        $this->cancelUrl = $cancelUrl;
        $this->currencyId = $currencyId;
        $this->returnUrl = $returnUrl;

        $this->billingAgreement = $billingAgreement;

        $this->payload = $payload;
    }

    /**
     * Set customer delivery address
     *
     * @param string $name        Name
     * @param string $street      Street
     * @param string $street2     Street 2
     * @param string $city        City
     * @param string $state       State
     * @param string $zip         Zip
     * @param string $countryCode CountryCode FR|US|UK
     *
     * @return $this
     */
    public function setCustomerDeliveryAddress($name, $street, $street2, $city, $state, $zip, $countryCode)
    {
        $this->isPaypalAddressOverrided = true;
        $this->name = $name;
        $this->street = $street;
        $this->street2 = $street2;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();
        $request .= '&PAYMENTREQUEST_0_AMT=' . urlencode(PaypalApiManager::convertFloatToNvpFormat($this->amount));
        $request .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($this->currencyId);
        $request .= '&RETURNURL=' . urlencode($this->returnUrl);
        $request .= '&CANCELURL=' . urlencode($this->cancelUrl);

        if ($this->isPaypalAddressOverrided) {
            $request .= '&ADDROVERRIDE=1';
            $request .= '&PAYMENTREQUEST_0_SHIPTONAME=' . urlencode($this->name);
            $request .= '&PAYMENTREQUEST_0_SHIPTOSTREET=' . urlencode($this->street);
            $request .= '&PAYMENTREQUEST_0_SHIPTOSTREET2=' . urlencode($this->street2);
            $request .= '&PAYMENTREQUEST_0_SHIPTOCITY=' . urlencode($this->city);
            $request .= '&PAYMENTREQUEST_0_SHIPTOSTATE=' . urlencode($this->state);
            $request .= '&PAYMENTREQUEST_0_SHIPTOZIP=' . urlencode($this->zip);
            $request .= '&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=' . urlencode($this->countryCode);
        }

        if ($this->billingAgreement != 0) {
            $request .= '&L_BILLINGTYPE0=MerchantInitiatedBillingSingleAgreement';
        }

        if (!empty($this->payload)) {
            $request .= '&' . PaypalApiManager::arrayToNvp($this->payload);
        }

        return $request;
    }
}
