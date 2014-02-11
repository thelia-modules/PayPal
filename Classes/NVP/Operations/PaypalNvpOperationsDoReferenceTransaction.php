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
 * Class PaypalNvpOperationsDoReferenceTransaction
 * Manage NVP DoReferenceTransaction Operation
 */
class PaypalNvpOperationsDoReferenceTransaction extends PaypalNvpOperationsBase
{
    const REQUEST_METHOD = 'DoReferenceTransaction';
    const PAYMENT_TYPE_INSTANT = 'INSTANT';

    /** @var string one of the transactionId already used by the client */
    protected $referenceId = null;

    /** @var float amount of the command */
    protected $amt = null;

    protected $paymentAction = null;

    /** @var string (EUR by default)*/
    protected $currencyCode = null;

    /**
     * Constructor
     *
     * @param PaypalApiCredentials $credentials   API Credentials (3T)
     * @param string               $referenceId   Transaction ID
     * @param float                $amt           Amount of the transaction
     * @param string               $paymentAction Payment action Sale|Authorization
     * @param string               $currencyCode  Currency EUR|USD
     */
    public function __construct(PaypalApiCredentials $credentials, $referenceId=null, $amt = 0, $paymentAction=PaypalApiManager::PAYMENT_TYPE_SALE, $currencyCode='EUR')
    {
        $this->operationName = self::REQUEST_METHOD;
        $this->credentials = $credentials;

        $this->referenceId = $referenceId;
        $this->amt = $amt;
        $this->paymentAction = $paymentAction;
        $this->currencyCode = $currencyCode;
    }

    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();

        $request .= '&REFERENCEID=' . urlencode($this->referenceId);
        $request .= '&PAYMENTACTION=' . urlencode($this->paymentAction);
        $request .= '&AMT=' . urlencode($this->amt);
        $request .= '&CURRENCYCODE=' . urlencode($this->currencyCode);
        $request .= '&BUTTONSOURCE=' . urlencode('Thelia_Cart_RT');

        return $request;
    }

    /**
     * Set reference id
     *
     * @param string $referenceId
     *
     * @return bool
     */
    public function setReferenceId($referenceId)
    {
        if ($referenceId !== $this->referenceId) {
            $this->referenceId = $referenceId;
        }

        return true;
    }

    /**
     * Get reference id
     *
     * @return string ReferenceId
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }
}
