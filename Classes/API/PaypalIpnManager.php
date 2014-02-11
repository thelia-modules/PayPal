<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/6/13
 * Time: 5:33 PM
 * 
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes\API;
/**
 * Class PaypalIpnManager
 * Assist in helping managing Paypal IPN
 */
class PaypalIpnManager
{
    /**
     * Send first IPN response call : 200 OK
     *
     * @param PaypalApiManager $paypalApiManager API manager
     *
     * @return bool
     */
    public function sendFirstResponse(PaypalApiManager $paypalApiManager)
    {
        $url = $paypalApiManager->getPayPalUrl();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        $caLocation = dirname(__FILE__) . '/../../cacert.pem';
        curl_setopt($ch, CURLOPT_CAINFO, $caLocation);
        if (!($res = curl_exec($ch))) {
            $paypalApiLogManager = new PaypalApiLogManager();
            $paypalApiLogManager->logText('Paypal Plugin : Got ' . curl_error($ch) . ' when processing IPN data', PaypalApiLogManager::ERROR);
            curl_close($ch);

            return false;
        }
        curl_close($ch);

        return true;
    }

    /**
     * Send second IPN response call : 200 OK + Paypal call
     *
     * @param PaypalApiManager $paypalApiManager API manager
     * @param string           $request          First request received from Paypal
     *
     * @return bool|string Response
     */
    public function sendSecondResponse(PaypalApiManager $paypalApiManager, $request)
    {
        $ch = curl_init($paypalApiManager->getPayPalUrl());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/../../cacert.pem');
        if (!($response = curl_exec($ch))) {
            $paypalApiLogManager = new PaypalApiLogManager();
            $paypalApiLogManager->logText("Paypal Plugin : Got ' . curl_error($ch) . ' when processing IPN data", PaypalApiLogManager::ERROR);
            curl_close($ch);

            return false;
        }
        curl_close($ch);

        return $response;
    }

    /**
     * Check Paypal IPN
     * check whether the payment_status is Completed
     * check that txn_id has not been previously processed
     * check that receiver_email is your Primary PayPal email
     * check that payment_amount/payment_currency are correct
     * process the notification
     *
     * @param PaypalApiErrorManager $errorManager Paypal module
     * @param string                $response     Second request received from Paypal
     *
     * @return bool
     */
    public function checkCheckoutIpn(PaypalApiErrorManager $errorManager, $response)
    {
        $paypalApiLogManager = new PaypalApiLogManager();
        $txnId = $_POST['txn_id'];

        if (strcmp($response, 'VERIFIED') == 0) {
            $itemName = $_POST['item_name'];
            $itemNumber = $_POST['item_number'];
            $paymentStatus = $_POST['payment_status'];
            $paymentAmount = $_POST['mc_gross'];
            $paymentCurrency = $_POST['mc_currency'];

            $receiverEmail = $_POST['receiver_email'];
            $payerEmail = $_POST['payer_email'];

            $paypalApiLogManager->logText("Paypal Plugin : IPN attempt $txnId : IPN response set as VALID by customer $payerEmail", PaypalApiLogManager::NOTICE);

            $checkout = new Commande();
            $checkout->charger_trans($itemName);

            return $errorManager->isCheckoutIpnValid($checkout, $paymentStatus, $txnId, $receiverEmail, $paymentAmount, $paymentCurrency);
        }

        $paypalApiLogManager->logText("Paypal Plugin : IPN attempt $txnId : IPN response set as INVALID", PaypalApiLogManager::NOTICE);

        return false;
    }

}