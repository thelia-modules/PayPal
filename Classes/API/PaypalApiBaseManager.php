<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/9/13
 * Time: 2:19 PM
 * 
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes\API;

use Paypal\Model\Config;
use Paypal\Paypal;

/**
 * Class PaypalApiBaseManager
 * Assist in managing PayPal feature
 */

abstract class PaypalApiBaseManager
{

    /** @var PaypalApiManager Manage Paypal API */
    protected $paypalApiManager = null;

    /** @var bool If sandbox mode is enabled */
    protected $isSandboxEnabled = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paypalApiManager = new PaypalApiManager(new Config(Paypal::JSON_CONFIG_PATH));
        $this->isSandboxEnabled = $this->paypalApiManager->isModeSandbox();
    }

    /**
     * Allow to log transactions
     *
     * @param string $response NVP response
     */
    protected function logTransaction($response)
    {
        $log = new PaypalApiLogManager('transaction');
        $log->logTransaction($response);

        return $this;
    }
}