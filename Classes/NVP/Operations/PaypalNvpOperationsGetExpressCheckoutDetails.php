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
