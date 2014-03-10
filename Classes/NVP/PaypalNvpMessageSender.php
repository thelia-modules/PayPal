<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/6/13
 * Time: 3:45 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
namespace Paypal\Classes\NVP;

use Paypal\Classes\NVP\Operations\PaypalNvpOperationInterface;
use Paypal\Classes\API\PaypalApiManager;
use Paypal\Model\PaypalConfig;

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
        $config = new PaypalConfig();
        $config->pushValues();
        $paypalApiManager = new PaypalApiManager($config);

        $url = $paypalApiManager->getApiUrl() . '?' . $this->message;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        return $response;
    }

}
