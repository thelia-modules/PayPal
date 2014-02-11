<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/9/13
 * Time: 2:19 PM
 * 
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes\API;

/**
 * Class PaypalApiOperationManager
 * Assist in managing Operations
 */
class PaypalApiOperationManager extends PaypalApiBaseManager
{
    /** @var NvpOperationInterface Operation to process  */
    protected $operation = null;

    /**
     * Constructor
     *
     * @param PaypalTheliaFacadeInterface $facade Retrieve data from Thelia
     */
    public function __construct(PaypalTheliaFacadeInterface $facade)
    {
        $this->facade = $facade;
        $this->paypalApiManager = new PaypalApiManager($this->link);
        $this->isSandboxEnabled = $this->paypalApiManager->isModeSandbox();
    }

    /**
     * Perform the given Operation
     *
     * @param NvpOperationInterface $operation Operation to process
     * @param bool                  $isSandbox If sandbox mode is enabled
     *
     * @return string
     */
    public function doOperation(NvpOperationInterface $operation, $isSandbox)
    {
        $nvpMessageSender = new PaypalNvpMessageSender($operation, $isSandbox);
        $response = $nvpMessageSender->send();
        $this->logTransaction($response);

        return $response;
    }

    /**
     * create SetExpressCheckout operation ready to be sent
     *
     * @param bool $isShortcut If the operation is done in shortcut mode (not in mark)
     *
     * @return PaypalNvpOperationsSetExpressCheckout Operation to process
     */
    public function createSetExpressCheckoutOperation($isShortcut = false)
    {
        $rows = $this->facade->getPaypalCustomizationOptions();
        $paypalCustomOptions = array();
        foreach ($rows as $key => $row) {
            // Cleaning Paypal doesn't want the #
            if (strpos(strtolower($key), 'color') !== false) {
                $row = ltrim($row, '#');
            }
            $paypalCustomOptions[$this->paypalApiManager->convertDatabaseKeyToNvpKey($key)] = $row;
        }
        unset($paypalCustomOptions[0]);

        $paymentAction = $this->facade->getPaymentType();

        $amountToPay = $this->getAmountToPay($isShortcut);

        $returnUrl = $this->getReturnUrl($isShortcut);

        /** @var PaypalNvpOperationsSetExpressCheckout $operation */
        $operation = new PaypalNvpOperationsSetExpressCheckout(
            new PaypalApiCredentials(new PayPalVariableRepository()),
            urlencode(
                PaypalApiManager::convertFloatToNvpFormat(
                    $amountToPay
                )
            ),
            $this->facade->getCurrency(),
            $returnUrl,
            $this->facade->getCancelUrl(),
            $this->isReferenceTransactionChosen(),
            array_merge(
                array(
                    'PAYMENTREQUEST_0_PAYMENTACTION' => $paymentAction,
                    'SOLUTIONTYPE' => 'Mark'
                ),
                $paypalCustomOptions,
                PaypalApiManager::nvpToArray(
                    $this->convertCartToNvp($isShortcut)
                )
            )
        );

        $addressDelivery = $this->facade->getAddressDelivery();
        if (false === $isShortcut) {
            $operation->setCustomerDeliveryAddress(
                $addressDelivery['name'],
                $addressDelivery['street'],
                $addressDelivery['street2'],
                $addressDelivery['city'],
                $addressDelivery['state'],
                $addressDelivery['zip'],
                $addressDelivery['countryCode']
            );
        }

        return $operation;
    }


    /**
     * Create DoExpressCheckoutPayment operation ready to be sent
     *
     * @param string $type   Operation type ECM|ECS|RT
     * @param float  $amount Total amount customer has to pay
     *
     * @return PaypalNvpOperationsDoExpressCheckoutPayment Operation to process
     */
    public function createDoExpressCheckoutPaymentOperation($type, $amount)
    {
        $payerId = '';
        $paymentAction = '';
        $token = '';
        $currentTransaction = $this->facade->getCurrentPaypalTransaction();
        if (isset($currentTransaction)) {
            if (isset($currentTransaction['payerId'])) {
                $payerId = $currentTransaction['payerId'];
            }
            if (isset($currentTransaction['paymentAction'])) {
                $paymentAction = $currentTransaction['paymentAction'];
            }
            if (isset($currentTransaction['token'])) {
                $token = $currentTransaction['token'];
            }
        }
        $ipnListenerUrl = $this->facade->getIpnCheckoutUrl();

        $buttonSource = $this->getButtonSource($type);

        /** @var PaypalNvpOperationsSetExpressCheckout $operation */
        $operation = new PaypalNvpOperationsDoExpressCheckoutPayment(
            new PaypalApiCredentials(
                new PayPalVariableRepository()
            ),
            urlencode(
                PaypalApiManager::convertFloatToNvpFormat(
                    $amount
                )
            ),
            $this->facade->getCurrency(),
            $payerId,
            $paymentAction,
            $token,
            $ipnListenerUrl,
            $buttonSource
        );

        return $operation;
    }

    /**
     * Create GetExpressCheckoutDetails operation ready to be sent
     *
     * @return PaypalNvpOperationsGetExpressCheckoutDetails Operation to process
     */
    public function createGetExpressCheckoutDetailOperation()
    {
        $payerId = null;
        $paymentAction = null;
        $currentTransaction = $this->facade->getCurrentPaypalTransaction();
        $token = '';
        if (isset($currentTransaction)) {
            if (isset($currentTransaction['token'])) {
                $token = $currentTransaction['token'];
            }
        }

        /** @var PaypalNvpOperationsGetExpressCheckoutDetails $operation */
        $operation = new PaypalNvpOperationsGetExpressCheckoutDetails(
            new PaypalApiCredentials(new PayPalVariableRepository()),
            urlencode(
                $token
            )
        );

        return $operation;
    }

    /**
     * Do a refund action on a command
     *
     * @param Commande $checkout Checkout to refuse
     *
     * @return Commande
     */
    public function doRefundAction(Commande $checkout)
    {
        $paypalApiLogManager = new PaypalApiLogManager();

        $refundOperation = new PaypalNvpOperationsRefund('104.0', new PaypalApiCredentials(new PayPalVariableRepository()), $checkout->transaction, 'EUR', PaypalNvpOperationsRefund::REFUND_TYPE_FULL);
        $sender= new PaypalNvpMessageSender($refundOperation);
        $response = $sender->send();

        //Si le remboursement c'est bien déroulé
        $ack = PaypalApiManager::extractFromQueryString($response, 'ACK');
        if ($ack == 'Success') {
            $transactionId = PaypalApiManager::extractFromQueryString($response, 'REFUNDTRANSACTIONID');
            $refundStatus = PaypalApiManager::extractFromQueryString($response, 'REFUNDSTATUS');

            $paypalCheckout = new PaypalCommande();
            $paypalCheckout->commande = $checkout->id;
            $paypalCheckout->transaction_id = $transactionId;
            $paypalCheckout->payment_type = strtoupper($refundStatus);
            $paypalCheckout->command_type = $this->facade->getPaymentType($paypalCheckout->commande);
            $paypalCheckout->action = PaypalNvpOperationsRefund::REQUEST_METHOD;
            $paypalCheckout->response = $response;
            $paypalCheckout->add();

            $statut = new Statut();
            $statut->charger_nom(Paypal::STATUT_REFUND);

            $checkout->transaction = $transactionId;
            $checkout->statut = $statut->id;
            $checkout->maj();

            $paypalApiLogManager->logText(
                "SUCCESS REFUND : transaction $transactionId has been refunded.",
                PaypalApiLogManager::NOTICE,
                'refund'
            );
        } else {
            $errorCode = PaypalApiManager::extractFromQueryString($response, 'L_ERRORCODE0');
            $errorlongMessage = PaypalApiManager::extractFromQueryString($response, 'L_LONGMESSAGE0');

            $paypalApiLogManager->logText('ERROR REFUND : transaction ' . $checkout->transaction . ' FAILED. ERROR CODE = ' . $errorCode . '. L_LONGMESSAGE0 = ' . $errorlongMessage . '. RESPONSE = ' . $response, PaypalApiLogManager::ERROR, 'refund');

            return $errorlongMessage;
        }

        return $checkout;
    }

    /**
     * Accept a payment
     *
     * @param Commande $checkout
     *
     * @return Commande
     */
    public function doCaptureAction(Commande $checkout)
    {
        $paypalApiLogManager = new PaypalApiLogManager();
        $checkout->total = $total = $checkout->total() - $checkout->remise + $checkout->port;

        //If a reference transaction has been done we need to use this transaction_id
        $paypalCustomer = new PaypalClient();
        $paypalCustomer->getPaypalClient($checkout->client);
        if ($paypalCustomer->billing_agreement != null && $paypalCustomer->billing_agreement != '') {
            $paypalCheckout = new PaypalCommande();
            $paypalCheckout->chargerCommandeAction($checkout->id, PaypalNvpOperationsDoReferenceTransaction::REQUEST_METHOD);

            if ($paypalCheckout->transaction_id != null) {
                $transactionId = $paypalCheckout->transaction_id;
            } else {
                $transactionId = $checkout->transaction;
            }

        } else {
            $transactionId = $checkout->transaction;
        }

        $doCapture = new PaypalNvpOperationsDoCapture(new PaypalApiCredentials(new PayPalVariableRepository()), $transactionId, $total, PaypalApiManager::COMPLETE_TYPE_COMPLETE);
        $sender = new PaypalNvpMessageSender($doCapture);
        $response = $sender->send();

        //If the payment has been captured 
        $ack = PaypalApiManager::extractFromQueryString($response, 'ACK');
        if ($ack == 'Success') {
            $transactionId = PaypalApiManager::extractFromQueryString($response, 'TRANSACTIONID');
            $paymentType = PaypalNvpOperationsDoReferenceTransaction::PAYMENT_TYPE_INSTANT;

            $paypalCheckout = new PaypalCommande();
            $paypalCheckout->commande = $checkout->id;
            $paypalCheckout->transaction_id = $transactionId;
            $paypalCheckout->payment_type = strtoupper($paymentType);
            $paypalCheckout->command_type = $this->facade->getPaymentType($paypalCheckout->commande);
            $paypalCheckout->action = PaypalNvpOperationsDoCapture::REQUEST_METHOD;
            $paypalCheckout->response = $response;
            $paypalCheckout->add();

            $checkout->transaction = $transactionId;
            $checkout->genfact();
            $checkout->statut = 2;

            $cnx = new Cnx();
            $checkout->link = $cnx->link;
            $checkout->maj();

            $_SESSION['navig']->commande = $checkout;

            $paypalApiLogManager->logText("SUCCESS DO_CAPTURE : transaction $transactionId has been captured.", PaypalApiLogManager::NOTICE, 'doCapture');
        } else {
            $errorCode = PaypalApiManager::extractFromQueryString($response, 'L_ERRORCODE0');
            $errorLongMessage = PaypalApiManager::extractFromQueryString($response, 'L_LONGMESSAGE0');

            $paypalApiLogManager->logText('ERROR DO_CAPTURE : transaction ' . $checkout->transaction. ' FAILED. ERROR CODE = ' . $errorCode . '. L_LONGMESSAGE0 = ' . $errorLongMessage . '. RESPONSE = ' . $response, PaypalApiLogManager::ERROR, 'doCapture');

            return $errorLongMessage;
        }

        return $checkout;
    }

    /**
     * Refuse a payment
     *
     * @param Commande $checkout Checkout to refuse
     *
     * @return Commande
     */
    public function doVoidAction(Commande $checkout)
    {
        $paypalApiLogManager = new PaypalApiLogManager();

        //If a reference transaction has been done we need to use this transaction_id
        $paypalClient = new PaypalClient();
        $paypalClient->getPaypalClient($checkout->client);
        if ($paypalClient->billing_agreement != null && $paypalClient->billing_agreement != '') {
            $paypalCommande = new PaypalCommande();
            $paypalCommande->chargerCommandeAction($checkout->id, PaypalNvpOperationsDoReferenceTransaction::REQUEST_METHOD);

            if ($paypalCommande->transaction_id != null) {
                $transactionId = $paypalCommande->transaction_id;
            } else {
                $transactionId = $checkout->transaction;
            }
        } else {
            $transactionId = $checkout->transaction;
        }

        $doVoid = new PaypalNvpOperationsDoVoid(new PaypalApiCredentials(new PayPalVariableRepository()), $transactionId);
        $message = new PaypalNvpMessageSender($doVoid);
        $response = $message->send();

        //If the payment has been captured 
        $ack = PaypalApiManager::extractFromQueryString($response, 'ACK');
        if ($ack == 'Success') {
            $transactionId = PaypalApiManager::extractFromQueryString($response, 'AUTHORIZATIONID');
            $paymentType = PaypalNvpOperationsDoReferenceTransaction::PAYMENT_TYPE_INSTANT;

            $paypalCommande = new PaypalCommande();
            $paypalCommande->commande = $checkout->id;
            $paypalCommande->transaction_id = $transactionId;
            $paypalCommande->payment_type = strtoupper($paymentType);
            $paypalCommande->command_type = $this->facade->getPaymentType($paypalCommande->commande);
            $paypalCommande->action = PaypalNvpOperationsDoVoid::REQUEST_METHOD;
            $paypalCommande->response = $response;
            $paypalCommande->add();

            $checkout->transaction = $transactionId;
            $checkout->statut = 5;
            $checkout->maj();

            $paypalApiLogManager->logText("SUCCESS DO_VOID : transaction $transactionId has been voided.", PaypalApiLogManager::NOTICE, 'doVoid');
        } else {
            $errorCode = PaypalApiManager::extractFromQueryString($response, 'L_ERRORCODE0');
            $errorLongMessage = PaypalApiManager::extractFromQueryString($response, 'L_LONGMESSAGE0');

            $paypalApiLogManager->logText('ERROR DO_VOID : transaction ' . $checkout->transaction . ' FAILED. ERROR CODE = ' . $errorCode . '. L_LONGMESSAGE0 = ' . $errorLongMessage . '. RESPONSE = ' . $response, PaypalApiLogManager::ERROR, 'doVoid');

            return $errorLongMessage;
        }

        return $checkout;
    }

    /**
     * Get return URL
     *
     * @param bool $isShortcut    If redirect to shortcut mode
     * @param bool $isDoReference If redirect to reference mode
     *
     * @return string
     */
    public function getReturnUrl($isShortcut, $isDoReference = false)
    {
        $doReferrenceParam = '';
        if ($isDoReference) {
            $doReferrenceParam = '&doReference=1';
        }

        if (true === $isShortcut) {
            $returnUrl = $this->facade->getUrlSite() . '/?fond=paypal_confirmation_payment&checkoutRef=' . $this->facade->getCheckoutReference() . '&type=ecs';
        } else {
            $returnUrl = $this->facade->getUrlSite() . '/?fond=paypal_confirmation_receipt&checkoutRef=' . $this->facade->getCheckoutReference() . $doReferrenceParam;
        }

        return $returnUrl;
    }

    /**
     * Get amount to pay
     *
     * @param bool $isShortcut
     *
     * @return float
     */
    public function getAmountToPay($isShortcut)
    {
        if ($isShortcut) {
            $amountToPay = $this->facade->getCartTotalAmount() + $this->facade->getDefaultShippingAmount();

            return $amountToPay;
        } else {
            $amountToPay = $this->facade->getTotalAmount();

            return $amountToPay;
        }
    }

    /**
     * Get delivery address
     *
     * @param string $nvpResponse NVP Response
     *
     * @return array
     */
    protected function getPaypalDeliveryAddress($nvpResponse)
    {
        $responseArray = PaypalApiManager::nvpToArray($nvpResponse);

        $addressArray = array();

        $addressArray['SHIPTONAME'] = $responseArray['SHIPTONAME'];
        $addressArray['SHIPTOSTREET'] = $responseArray['SHIPTOSTREET'];
        $addressArray['SHIPTOCITY'] = $responseArray['SHIPTOCITY'];
        $addressArray['SHIPTOSTATE'] = $responseArray['SHIPTOSTATE'];
        $addressArray['SHIPTOZIP'] = $responseArray['SHIPTOZIP'];
        $addressArray['SHIPTOCOUNTRYCODE'] = $responseArray['SHIPTOCOUNTRYCODE'];
        $addressArray['SHIPTOCOUNTRYNAME'] = $responseArray['SHIPTOCOUNTRYNAME'];

        return $addressArray;
    }

    /**
     * Check if customer chose to perform a reference transaction
     * in order to get a billing agreement
     *
     * @return int
     */
    protected function isReferenceTransactionChosen()
    {
        if (isset($_POST['memo-paypal']) && $_POST['memo-paypal'] == 1) {
            return 1;
        }

        return 0;
    }

    /**
     * Get Button Source code
     *
     * @param string $type Operation type ECM|ECS|RT
     *
     * @return string
     */
    protected function getButtonSource($type)
    {
        $code = false;
        switch ($type) {
            case 'ECM':
                $code = 'Thelia_Cart_ECM';
                break;
            case 'ECS':
                $code = 'Thelia_Cart_ECS';
                break;
            case 'RT':
                $code = 'Thelia_Cart_RT';
                break;
            default:
        }

        return $code;
    }

}
