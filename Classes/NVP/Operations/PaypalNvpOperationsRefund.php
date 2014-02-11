<?php
/**
 * @author Jérôme BILLIRAS <jbilliras@openstudio.fr>
 */
namespace Paypal\Classes\NVP\Operations;

use Paypal\Classes\API\PaypalApiCredentials;
class PaypalNvpOperationsRefund extends PaypalNvpOperationsBase
{
    const REQUEST_METHOD = 'RefundTransaction';
    const REFUND_TYPE_FULL = 'Full';
    const REFUND_TYPE_PARTIAL = 'Partial';
    const REFUND_TYPE_EXTERNAL_DISPUTE = 'ExternalDispute';
    const REFUND_TYPE_OTHER = 'Other';

    /** @var string Transaction id 17 single-byte alphanumeric chars */
    protected $transactionId = null;

    /** @var string Currency code ex: EUR */
    protected $currencyCode = null;

    /** @var string Refund type, one of self::REFUND_TYPE_* const */
    private $refundType = self::REFUND_TYPE_FULL;

    /** @var string Refund amount
     * Must be specified as 2000.00 or 2,000.00.
     * The specified amount cannot exceed USD $10,000.00, regardless of the currency used.
     */
    protected $refundAmount = null;



    /**
     * Constructor
     *
     * @param string               $version       API version
     * @param PaypalApiCredentials $credentials   API Credentials (3T)
     * @param string               $transactionId Transaction ID
     * @param string               $currencyCode  Currency code (EUR)
     * @param string               $refundType    Refund type
     * @param string               $refundAmount  Refund amount (<USD $10,000.00)
     */
    public function __construct($version, PaypalApiCredentials $credentials, $transactionId, $currencyCode, $refundType = self::REFUND_TYPE_FULL, $refundAmount = null)
    {
        $this->operationName = self::REQUEST_METHOD;
        $this->operationVersion = $version;
        $this->credentials = $credentials;

        $this->transactionId = $transactionId;
        $this->currencyCode = $currencyCode;
        $this->setRefundType($refundType);
        $this->refundAmount = $refundAmount;
    }

    /**
     * Set refund type
     *
     * @param string $refundType Refund type self::REFUND_TYPE_FULL|self::REFUND_TYPE_PARTIAL|self::REFUND_TYPE_EXTERNAL_DISPUTE|self::REFUND_TYPE_OTHER
     *
     * @return bool
     */
    public function setRefundType($refundType)
    {
        if ($refundType !== $this->refundType) {
            if (in_array($refundType, array(self::REFUND_TYPE_FULL, self::REFUND_TYPE_PARTIAL, self::REFUND_TYPE_EXTERNAL_DISPUTE, self::REFUND_TYPE_OTHER), true)) {
                $this->refundType = $refundType;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get refund type
     *
     * @return string Refund type self::REFUND_TYPE_FULL|self::REFUND_TYPE_PARTIAL|self::REFUND_TYPE_EXTERNAL_DISPUTE|self::REFUND_TYPE_OTHER
     */
    public function getRefundType()
    {
        return $this->refundType;
    }


    /**
     * {@inheritdoc }
     */
    public function getRequest()
    {
        $request = parent::getRequest();

        $request .= '&TRANSACTIONID='. urlencode($this->transactionId);
        $request .= '&CURRENCYCODE='. urlencode($this->currencyCode);
        $request .= '&REFUNDTYPE='. urlencode($this->refundType);
        if ($this->refundType === self::REFUND_TYPE_PARTIAL) {
            $request .= '&AMT='. urlencode(PaypalApiManager::convertFloatToNvpFormat($this->refundAmount));
        }
        //$request .= '&PAYERID='. ;
        //$request .= '&INVOICEID='. ;
        //$request .= '&NOTE='. ;
        //$request .= '&RETRYUNTIL='. ;
        //$request .= '&REFUNDSOURCE='. ;
        //$request .= '&MERCHANTSTOREDETAILS='. ;
        //$request .= '&REFUNDADVICE='. ;
        //$request .= '&REFUNDITEMDETAILS='. ;
        //$request .= '&MSGSUBID='. ;

        return $request;
    }
}