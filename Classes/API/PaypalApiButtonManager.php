<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 8/9/13
 * Time: 2:19 PM
 * 
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */


namespace Paypal\Classes\API;
use Paypal\Classes\PaypalTheliaFacadeInterface;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsBase;
use Paypal\Classes\NVP\Operations\PaypalNvpOperationsBmCreateButton;
/**
 * Class PaypalApiButtonManager
 * Assist in managing PayPal Button
 */
class PaypalApiButtonManager extends PaypalApiBaseManager
{
    /**
     * Constructor
     *
     * @param PaypalTheliaFacadeInterface $facade Retrieve data from Thelia
     */
    public function __construct(PaypalTheliaFacadeInterface $facade)
    {
        parent::__construct($facade);

        $this->operationManager = new PaypalApiOperationManager($facade);
    }

    /**
     * Assist in building PayPal Buttons
     *
     * @param PaypalNvpOperationsBase $operation Button Operation
     * @param bool                    $form      If the button is a HTML form or a link
     *
     * @return string HTML form or HTML link for mail
     */
    public function createButton(PaypalNvpOperationsBase $operation, $form = true)
    {

//        $rows = $this->facade->getPaypalCustomizationOptions();
//        $paypalCustomOptions = array();
//        foreach ($rows as $key => $row) {
//            // Cleaning Paypal doesn't want the #
//            if (strpos(strtolower($key), 'color') !== false) {
//                $row = ltrim($row, '#');
//            }
//            $paypalCustomOptions[$this->paypalApiManager->convertDatabaseKeyToNvpKey($key)] = $row;
//        }
//        unset($paypalCustomOptions[0]);
//
//        $operation->addExtraPayload($paypalCustomOptions);

        $urlSite = new Variable();
        $paypalApiManager = new PaypalApiManager($urlSite->link);
        $response = $this->operationManager->doOperation($operation, $paypalApiManager->isModeSandbox());

        $parsedResponse = PaypalApiManager::nvpToArray($response);

        $button = '';
        if ($form) {
            if (isset($parsedResponse)) {
                $button = $parsedResponse['WEBSITECODE'];
            }
        } else {
            if (isset($parsedResponse)) {
                $button = htmlentities($parsedResponse['EMAILLINK']);
            }
        }

        return $button;
    }

    /**
     * Create BMCreateButton operation ready to be sent
     *
     * @param float  $productAmount Product amount without shipping fee
     * @param string $productRef    Product reference
     * @param string $productName   Product name
     *
     * @return PaypalNvpOperationsBase BmCreateButton Operation
     */
    public function createBmCreateButtonOperation($productAmount, $productRef, $productName)
    {
        $operation = new PaypalNvpOperationsBmCreateButton();
        $shippingAmount = $this->facade->getDefaultShippingAmount();
        $operation->setCartInfo($productAmount + $shippingAmount, 0.00, $shippingAmount, 0.00);

        $vendorInfo = $this->facade->getVendorInfoToCustomizePaypal();

        $operation->setProcessInfo(
            $this->facade->getIpnCheckoutUrl(),
            $this->facade->getCancelUrl(),
            $this->facade->getUrlSite() . '?fond=paypal_confirmation_payment',
            $this->facade->getPaymentType(),
            $this->facade->getCurrency(),
            0
        )->setVendorInfo(
            $vendorInfo['body_bg_color'],
            $vendorInfo['logo_image'],
            $vendorInfo['logo_text']
        );

        $operation->addExtraPayload(
            array(
                'amount' => PaypalApiManager::convertFloatToNvpFormat($productAmount),
                'item_name' => $productName . ' (' . $productRef . ')',
            )
        );

        return $operation;
    }

}
