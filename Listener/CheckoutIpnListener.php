<?php

namespace Paypal\Listener;

use Paypal\Classes\API\PaypalIpnManager;
use Paypal\Classes\API\PaypalApiErrorManager;
use Paypal\Paypal;
use Paypal\Model\Config;
use Paypal\Classes\API\PaypalApiManager;

class CheckoutIpnListener {
    public function listen() {
        // @see https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNIntro/
        $paypalIpnManager = new PaypalIpnManager();
        $errorManager = new PaypalApiErrorManager();

        // STEP 2: read POST data

        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        $rawPostData = file_get_contents('php://input');
        $rawPostArray = explode('&', $rawPostData);
        $myPost = array();
        foreach ($rawPostArray as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        // Read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $request = 'cmd=_notify-validate';

        $getMagicQuotesExists = function_exists('get_magic_quotes_gpc');

        foreach ($myPost as $key => $value) {
            if ($getMagicQuotesExists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $request .= "&$key=$value";
        }

        $paypalApiManager = new PaypalApiManager(new Config(Paypal::JSON_CONFIG_PATH));

        // STEP 3: send 200 OK
        if ($paypalIpnManager->sendFirstResponse($paypalApiManager)) {
            // Step 4: POST IPN data back to PayPal to validate
            $response = $paypalIpnManager->sendSecondResponse($paypalApiManager, $request);

            // @todo finalize IPN manager
            // $paypalIpnManager->checkCheckoutIpn($errorManager, $response);
        }
    }
}