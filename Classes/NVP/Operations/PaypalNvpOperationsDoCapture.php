<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/5/13
 * Time: 5:36 PM
 *
 * @author Guillaume BARRAL <gbarral@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;
use Paypal\Classes\API\PaypalApiCredentials;
use Paypal\Classes\API\PaypalApiManager;
/**
 * Class PaypalNvpOperationsDoCapture
 * Manage NVP DoCapture Operation
 */
class PaypalNvpOperationsDoCapture extends PaypalNvpOperationsBase
{
    const REQUEST_METHOD = 'DoCapture';

    /** @var string one of the transactionId used by the client's command */
    protected $authorizationId = null;

    /** @var float amount of the command */
    protected $amt = null;

    /** @var string complete or notComplete annulation */
    protected $completeType = null;

    /** @var string (EUR by default)*/
    protected $currencyCode = null;

    /**
     * Constructor
     *
     * @param string               $version       API version
     * @param PaypalApiCredentials $credentials   API Credentials (3T)
     * @param string               $referenceId   Transaction ID
     */
    public function __construct(PaypalApiCredentials $credentials, $authorizationId=null, $amt = 0, $completeType=PaypalApiManager::COMPLETE_TYPE_COMPLETE, $currencyCode='EUR')
    {
        $this->operationName = self::REQUEST_METHOD;
        $this->credentials = $credentials;

        $this->authorizationId = $authorizationId;
        $this->amt = $amt;
        $this->completeType = $completeType;
        $this->currencyCode = $currencyCode;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();

        $request .= '&AUTHORIZATIONID='. urlencode($this->authorizationId);
        $request .= '&AMT='. urlencode($this->amt);
        $request .= '&COMPLETETYPE='. urlencode($this->completeType);
        $request .= '&CURRENCYCODE='. urlencode($this->currencyCode);

        return $request;
    }
}
