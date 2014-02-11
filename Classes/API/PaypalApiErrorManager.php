<?php
/**
 * @link https://www.x.com/developers/paypal/documentation-tools/api/getexpresscheckoutdetails-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/doexpresscheckoutpayment-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/setexpresscheckout-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/gettransactiondetails-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/createrecurringpaymentsprofile-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/getrecurringpaymentsprofiledetails-api-operation-nvp
 *
 * L_ERRORCODE: @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_errorcodes
 * ACK: @link https://www.x.com/content/paypal-nvp-api-overview
 */

namespace Paypal\Classes\API;

use Thelia\Model\Order;

class PaypalApiErrorManager
{
    CONST ACK_SUCCESS = 'Success';

    CONST ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';

    CONST ACK_FAILURE = 'Failure';

    CONST ACK_FAILUREWITHWARNING = 'FailureWithWarning';

    CONST ACK_WARNING = 'Warning';

    CONST CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED = 'PaymentActionNotInitiated';

    CONST CHECKOUTSTATUS_PAYMENT_ACTION_FAILED = 'PaymentActionFailed';

    CONST CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS = 'PaymentActionInProgress';

    CONST CHECKOUTSTATUS_PAYMENT_COMPLETED = 'PaymentCompleted';

    CONST CHECKOUTSTATUS_PAYMENT_ACTION_COMPLETED = 'PaymentActionCompleted';

    /**
     * No status
     */
    CONST PAYMENTSTATUS_NONE = 'None';

    /**
     * A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.
     */
    CONST PAYMENTSTATUS_CANCELED_REVERSAL = 'Canceled-Reversal';

    /**
     * The payment has been completed, and the funds have been added successfully to your account balance.
     */
    CONST PAYMENTSTATUS_COMPLETED = 'Completed';

    /**
     * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the PendingReason element.
     */
    CONST PAYMENTSTATUS_DENIED = 'Denied';

    /**
     * The authorization period for this payment has been reached.
     */
    CONST PAYMENTSTATUS_EXPIRED = 'Expired';

    /**
     * The payment has failed. This happens only if the payment was made from your buyer's bank account.
     */
    CONST PAYMENTSTATUS_FAILED = 'Failed';

    /**
     * The transaction has not terminated, e.g. an authorization may be awaiting completion.
     */
    CONST PAYMENTSTATUS_IN_PROGRESS = 'In-Progress';

    /**
     * The payment has been partially refunded.
     */
    CONST PAYMENTSTATUS_PARTIALLY_REFUNDED = 'Partially-Refunded';

    /**
     * The payment is pending. See the PendingReason field for more information.
     */
    CONST PAYMENTSTATUS_PENDING = 'Pending';

    /**
     * You refunded the payment.
     */
    CONST PAYMENTSTATUS_REFUNDED = 'Refunded';

    /**
     * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
     */
    CONST PAYMENTSTATUS_REVERSED = 'Reversed';

    /**
     *  A payment has been accepted.
     */
    CONST PAYMENTSTATUS_PROCESSED = 'Processed';

    /**
     * An authorization for this transaction has been voided.
     */
    CONST PAYMENTSTATUS_VOIDED = 'Voided';

    /**
     * The payment has been completed, and the funds have been added successfully to your pending balance.
     */
    CONST PAYMENTSTATUS_COMPLETED_FUNDS_HELD = 'Completed-Funds-Held';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Sale – This is a final sale for which you are requesting payment (default).
     */
    CONST PAYMENTACTION_SALE = 'Sale';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Authorization – This payment is a basic authorization subject to settlement with PayPal Authorization and Capture.
     */
    CONST PAYMENTACTION_AUTHORIZATION = 'Authorization';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Order – This payment is an order authorization subject to settlement with PayPal Authorization and Capture.
     */
    CONST PAYMENTACTION_ORDER = 'Order';

    /**
     * Payment has not been authorized by the user.
     */
    CONST L_ERRORCODE_PAYMENT_NOT_AUTHORIZED = 10485;

    /**
     * PayPal displays the shipping address on the PayPal pages.
     */
    CONST NOSHIPPING_DISPLAY_ADDRESS = 0;

    /**
     * PayPal does not display shipping address fields whatsoever.
     */
    CONST NOSHIPPING_NOT_DISPLAY_ADDRESS = 1;

    /**
     * If you do not pass the shipping address, PayPal obtains it from the buyer’s account profile.
     */
    CONST NOSHIPPING_DISPLAY_BUYER_ADDRESS = 2;

    /**
     * You do not require the buyer’s shipping address be a confirmed address.
     * For digital goods, this field is required, and you must set it to 0.
     * Setting this field overrides the setting you specified in your Merchant Account Profile.
     */
    CONST REQCONFIRMSHIPPING_NOT_REQUIRED = 0;

    /**
     * You require the buyer’s shipping address be a confirmed address.
     * Setting this field overrides the setting you specified in your Merchant Account Profile.
     */
    CONST REQCONFIRMSHIPPING_REQUIRED = 1;

    /**
     * Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive, and m specifies the list item within the payment; except for digital goods, which only supports single payments.
     */
    CONST PAYMENTREQUEST_ITERMCATEGORY_DIGITAL = 'Digital';

    /**
     * Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive, and m specifies the list item within the payment; except for digital goods, which only supports single payments.
     */
    CONST PAYMENTREQUEST_ITERMCATEGORY_PHYSICAL = 'Physical';

    /**
     * Indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.
     *
     * PayPal does not automatically bill the outstanding balance.
     */
    CONST AUTOBILLOUTAMT_NOAUTOBILL = 'NoAutoBill';

    /**
     * Indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.
     *
     * PayPal automatically bills the outstanding balance.
     */
    CONST AUTOBILLOUTAMT_ADDTONEXTBILLING = 'AddToNextBilling';

    CONST BILLINGPERIOD_DAY = 'Day';

    CONST BILLINGPERIOD_WEEK = 'Week';

    /**
     * For SemiMonth, billing is done on the 1st and 15th of each month.
     */
    CONST BILLINGPERIOD_SEMIMONTH = 'SemiMonth';

    CONST BILLINGPERIOD_MONTH = 'Month';

    CONST BILLINGPERIOD_YEAR = 'Year';

    /**
     * By default, PayPal suspends the pending profile in the event that the initial payment amount fails. You can override this default behavior by setting this field to ContinueOnFailure. Then, if the initial payment amount fails, PayPal adds the failed payment amount to the outstanding balance for this recurring payment profile.
     */
    CONST FAILEDINITAMTACTION_CONTINUEONFAILURE = 'ContinueOnFailure';

    /**
     * If this field is not set or you set it to CancelOnFailure, PayPal creates the recurring payment profile, but places it into a pending status until the initial payment completes. If the initial payment clears, PayPal notifies you by IPN that the pending profile has been activated. If the payment fails, PayPal notifies you by IPN that the pending profile has been canceled.
     */
    CONST FAILEDINITAMTACTION_CANCELONFAILURE = 'CancelOnFailure';

    CONST CREDITCARDTYPE_VISA = 'Visa';

    CONST CREDITCARDTYPE_MASTERCARD = 'MasterCard';

    CONST CREDITCARDTYPE_DISCOVER = 'Discover';

    CONST CREDITCARDTYPE_AMEX = 'Amex';

    /**
     * If the credit card type is Maestro, you must set CURRENCYCODE to GBP. In addition, you must specify either STARTDATE or ISSUENUMBER.
     */
    CONST CREDITCARDTYPE_MAESTRO = 'Maestro';

    CONST PAYERSTATUS_VERIFIED = 'verified';

    CONST PAYERSTATUS_UNVERIFIED = 'unverified';

    /**
     * The recurring payment profile has been successfully created and activated for scheduled payments according the billing instructions from the recurring payments profile.
     */
    CONST PROFILESTATUS_ACTIVEPROFILE = 'ActiveProfile';

    /**
     * The system is in the process of creating the recurring payment profile. Please check your IPN messages for an update.
     */
    CONST PROFILESTATUS_PENDINGPROFILE = 'PendingProfile';

    /**
     * Type of billing agreement. For recurring payments, this field must be set to RecurringPayments. In this case, you can specify up to ten billing agreements. Other defined values are not valid.
     */
    CONST BILLINGTYPE_RECURRING_PAYMENTS = 'RecurringPayments';

    /**
     * Type of billing agreement for reference transactions. You must have permission from PayPal to use this field. This field must be set to one of the following values:
     *
     * PayPal creates a billing agreement for each transaction associated with buyer. You must specify version 54.0 or higher to use this option.
     */
    CONST BILLINGTYPE_MERCHANTINITIATEDBILLING = 'MerchantInitiatedBilling';

    /**
     * Type of billing agreement for reference transactions. You must have permission from PayPal to use this field. This field must be set to one of the following values:
     *
     * PayPal creates a single billing agreement for all transactions associated with buyer. Use this value unless you need per-transaction billing agreements. You must specify version 58.0 or higher to use this option.
     */
    CONST BILLINGTYPE_MERCHANTINITIATEDBILLINGSINGLEAGREEMENT = 'MerchantInitiatedBilling';

    CONST RECURRINGPAYMENTSTATUS_ACTIVE = 'Active';

    CONST RECURRINGPAYMENTSTATUS_PENDING = 'Pending';

    CONST RECURRINGPAYMENTSTATUS_CANCELLED = 'Cancelled';

    CONST RECURRINGPAYMENTSTATUS_SUSPENDED = 'Suspended';

    CONST RECURRINGPAYMENTSTATUS_EXPIRED = 'Expired';

    CONST RECURRINGPAYMENTSTATUS_REACTIVATE = 'Reactivate';

    CONST RECURRINGPAYMENTACTION_CANCEL = 'Cancel';

    CONST RESPONSE_KEY_TOKEN = 'TOKEN';
    CONST RESPONSE_KEY_CORRELATIONID = 'CORRELATIONID';

    /**
     * Check if the SetExpressCheckout API response show a success or not
     *
     * @param string $response query string
     *
     * @return bool
     */
    public static function isSetExpressCheckoutResponseSuccess($response)
    {
        $return = true;
        $response = PaypalApiManager::nvpToArray($response);

        if (false == ($response['ACK'] == self::ACK_SUCCESS || $response['ACK'] ==  self::ACK_SUCCESS_WITH_WARNING)) {
            $return = false;
        }

        return $return;
    }

    /**
     * @param Order $checkout      Order
     * @param string $paymentStatus   Completed|Pending|Denied
     * @param string $txnId           Transaction ID
     * @param string $receiverEmail   Paypal account email
     * @param float  $paymentAmount   Payment amount
     * @param string $paymentCurrency Payment currency
     * @param string $pendingReason   Pending reason : authorization
     *
     * @return bool
     */
    public function isCheckoutIpnValid(Order $checkout, $paymentStatus, $txnId, $receiverEmail, $paymentAmount, $paymentCurrency, $pendingReason=null)
    {
        if ($this->isPaymentStatusValid($paymentStatus, $pendingReason, $txnId) &&
            $this->isTransactionIDValid($txnId) &&
            $this->isPaypalAccountEmailValid($receiverEmail, $txnId) &&
            $this->isAmountValid($checkout, $paymentAmount, $txnId) &&
            $this->isCurrencyValid($checkout, $paymentCurrency, $txnId)
        ) {
            // Set checkout as paid
            $paypalModule = new Paypal();
            $paypalModule->setCheckoutAsPaid($checkout);

            return true;
        }

        return false;
    }

    /**
     * Check if IPN currency is valid
     *
     * @param Order $checkout        Checkout
     * @param string   $paymentCurrency Currency
     * @param string   $txnId           Transaction ID
     *
     * @return bool
     */
    public function isCurrencyValid(Order $checkout, $paymentCurrency, $txnId)
    {
        $currency = new Devise($checkout->devise);
        $code = $currency->code;
        if ($code == $paymentCurrency) {
            return true;
        }

        $paypalApiLogManager = new PaypalApiLogManager();
        $paypalApiLogManager->logText("Paypal Plugin : Check attempt $txnId : bad currency (expected=$code, received=$paymentCurrency)", PaypalApiLogManager::WARNING);

        return false;
    }

    /**
     * Check if IPN amount is valid
     *
     * @param Order $checkout      Checkout
     * @param float    $paymentAmount Amount
     * @param string   $txnId         Transaction ID
     *
     * @return bool
     */
    public function isAmountValid(Order $checkout, $paymentAmount, $txnId)
    {
        $paymentAmount = round($paymentAmount, 2);
        $total = round($checkout->total + $checkout->port - $checkout->remise, 2);
        if ($total == $paymentAmount) {
            return true;
        }

        $paypalApiLogManager = new PaypalApiLogManager();
        $paypalApiLogManager->logText("Paypal Plugin : Check attempt $txnId : bad amount (expected=$total, received=$paymentAmount)", PaypalApiLogManager::WARNING);

        return false;
    }

    /**
     * Check if IPN paypal account email is valid
     *
     * @param string $receiverEmail Paypal account email
     * @param string $txnId         Transaction ID
     *
     * @return bool
     */
    public function isPaypalAccountEmailValid($receiverEmail, $txnId)
    {
        $paypalEmail = new Variable();
        $paypalEmail->charger('paypaloffi_configuration_email');
        $email = $paypalEmail->valeur;

        if ($email == $receiverEmail) {
            return true;
        }

        $paypalApiLogManager = new PaypalApiLogManager();
        $paypalApiLogManager->logText("Paypal Plugin : Check attempt $txnId : bad paypal account email (expected=$email, received=$receiverEmail)", PaypalApiLogManager::WARNING);

        return false;
    }

    /**
     * Check if IPN transaction id is valid
     *
     * @param string $txnId Transaction ID
     *
     * @return bool
     */
    public function isTransactionIDValid($txnId)
    {
        $cnx = new Cnx();
        $query = 'SELECT COUNT(*) FROM paypal_ipn WHERE `transaction_id` = ' . $txnId . ' AND (`status` = `Completed` OR `status` = `Denied`)';
        $result = mysql_query($query, $cnx->link);

        if ($result['COUNT(*)'] == 0) {
            return true;
        }

        $paypalApiLogManager = new PaypalApiLogManager();
        $paypalApiLogManager->logText("Paypal Plugin : Check attempt $txnId : transaction ID already exists", PaypalApiLogManager::WARNING);

        return false;

    }

    /**
     * Check if IPN payment status is valid
     *
     * @param string $paymentStatus Completed|Pending|Denied
     * @param string $pendingReason Pending reason : authorization
     * @param string $txnId         Transaction ID
     *
     * @return bool
     */
    public function isPaymentStatusValid($paymentStatus, $pendingReason, $txnId)
    {
        if (self::PAYMENTSTATUS_COMPLETED == $paymentStatus
            || (self::PAYMENTSTATUS_PENDING == $paymentStatus && $pendingReason == 'authorization')) {
            return true;
        } else {
            $paypalApiLogManager = new PaypalApiLogManager();
            $paypalApiLogManager->logText("Paypal Plugin : Check attempt $txnId : payment status not set as 'Completed'", PaypalApiLogManager::WARNING);

            return false;
        }
    }

}