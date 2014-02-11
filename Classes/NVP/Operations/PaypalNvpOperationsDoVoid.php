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
/**
 * Class PaypalNvpOperationsDoVoid
 * Manage NVP DoVoid Operation
 */
class PaypalNvpOperationsDoVoid extends PaypalNvpOperationsBase
{
    const REQUEST_METHOD = 'DoVoid';
    
    /** @var string one of the transactionId used by the client's command */
    protected $authorizationid = null;
    
    /** @var string description of this action */
    protected $note = null;
    
    /**
     * Constructor
     *
     * @param string               $version       API version
     * @param PaypalApiCredentials $credentials   API Credentials (3T)
     * @param string               $referenceId   Transaction ID
     */
    public function __construct(PaypalApiCredentials $credentials, $authorizationid=null, $note="")
    {
        $this->operationName = self::REQUEST_METHOD;
        $this->credentials = $credentials;

        $this->authorizationid = $authorizationid;
        
        if($note == "")
            $note = "Command canceled by the admin.";
            
        $this->note = $note;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();

        $request .= '&AUTHORIZATIONID='. urlencode($this->authorizationid);
        $request .= '&NOTE='. urlencode($this->note);

        return $request;
    }
}
