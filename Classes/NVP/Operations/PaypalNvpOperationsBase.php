<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/12/13
 * Time: 2:17 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;

use Paypal\Classes\API\PaypalApiManager;

abstract class PaypalNvpOperationsBase implements PaypalNvpOperationInterface
{
    /** @var \Paypal\Classes\API\PaypalApiCredentials API Credentials (3T) */
    protected $credentials = null;

    /** @var string operation name  */
    protected $operationName = null;

    /** @var array Payload with optional parameters */
    protected $payload = null;

    /**
     * Generate NVP request message
     *
     * @return string NVP string
     */
    public function getRequest()
    {
        $request = 'METHOD=' . $this->operationName;
        $request .= '&VERSION=' . PaypalApiManager::API_VERSION;
        $request .= '&USER=' . urlencode($this->credentials->getApiUsername());
        $request .= '&PWD=' . urlencode($this->credentials->getApiPassword());
        $request .= '&SIGNATURE=' . urlencode($this->credentials->getApiSignature());

        return $request;
    }

    /**
     * Get Operation Name
     *
     * @return string Operation name
     */
    public function getOperationName()
    {
        return $this->operationName;
    }
}
