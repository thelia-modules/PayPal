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


}