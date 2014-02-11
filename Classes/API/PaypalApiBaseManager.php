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
use Paypal\Model\Config;
use Paypal\Paypal;

/**
 * Class PaypalApiBaseManager
 * Assist in managing PayPal feature
 */

abstract class PaypalApiBaseManager
{

    /** @var PaypalTheliaFacadeInterface Retrieve data from Thelia  */
    protected $facade = null;

    /** @var PaypalApiManager Manage Paypal API */
    protected $paypalApiManager = null;

    /** @var bool If sandbox mode is enabled */
    protected $isSandboxEnabled = true;

    /**
     * Constructor
     *
     * @param PaypalTheliaFacadeInterface $facade Retrieve data from Thelia
     */
    public function __construct(PaypalTheliaFacadeInterface $facade)
    {
        $this->facade = $facade;
        $this->paypalApiManager = new PaypalApiManager(new Config(Paypal::JSON_CONFIG_PATH));
        $this->isSandboxEnabled = $this->paypalApiManager->isModeSandbox();
    }

    /**
     * Return a cloned instance on the Facade
     *
     * @return PaypalTheliaFacadeInterface
     */
    public function getFacade()
    {
        return clone $this->facade;
    }

    /**
     * Convert a Cart into a NVP request
     *
     * @param bool $isShortcut If the operation is done in shortcut mode (not in mark)
     *
     * @throws \InvalidArgumentException
     * @return string NVP string representing our Cart
     */
    protected function convertCartToNvp($isShortcut)
    {
        if ($this->facade->getNbArticles() > 10) {
            throw new \InvalidArgumentException('PayPal max Cart product is 10.');
        }

        $cartNvpString = '';
        $cartNvpString .= $this->processProductsSoldList($cartNvpString, $isShortcut);

        if ($isShortcut) {
            $totalAmount = $this->facade->getCartTotalAmount() - $this->facade->getDiscountAmount();
        } else {
            $totalAmount = $this->facade->getProductsAmount() - $this->facade->getDiscountAmount();
        }

        $totalAmount = urlencode(
            PaypalApiManager::convertFloatToNvpFormat(
                $totalAmount
            )
        );
        $cartNvpString .= "PAYMENTREQUEST_0_ITEMAMT=$totalAmount&";



        if ($isShortcut) {
            $shippingAmount = $this->facade->getDefaultShippingAmount();
        } else {
            $shippingAmount = $this->facade->getShippingAmount();
        }
        $shippingAmount = urlencode(
            PaypalApiManager::convertFloatToNvpFormat(
                $shippingAmount
            )
        );
        $cartNvpString .= "PAYMENTREQUEST_0_SHIPPINGAMT=$shippingAmount&";

        return substr($cartNvpString, 0, -1);
    }

//    /**
//     * Convert a Cart into a NVP request
//     *
//     * @throws InvalidArgumentException
//     * @return string NVP string representing our Cart
//     */
//    protected function convertPaypalCustomizationOptionsToNvp()
//    {
//        $cartNvpString = '';
//
//        $cartNvpString .= $this->processProductsSoldList($cartNvpString);
//
//        $totalAmount = urlencode(
//            PaypalApiManager::convertFloatToNvpFormat(
//                $this->facade->getProductsAmount() - $this->facade->getDiscountAmount()
//            )
//        );
//        $cartNvpString .= "PAYMENTREQUEST_0_ITEMAMT=$totalAmount&";
//
//        $shippingAmount = urlencode(
//            PaypalApiManager::convertFloatToNvpFormat(
//                $this->facade->getShippingAmount()
//            )
//        );
//        $cartNvpString .= "PAYMENTREQUEST_0_SHIPPINGAMT=$shippingAmount&";
//
//        return substr($cartNvpString, 0, -1);
//    }

    /**
     * Allow to log transactions
     *
     * @param string $response NVP response
     *
     * @return PaypalApiOperationManager
     */
    protected function logTransaction($response)
    {
        $log = new PaypalApiLogManager('transaction');
        $log->logTransaction($response);

        return $this;
    }

    /**
     * Process VenteProd MySQL result into NVP
     *
     * @param string $cartNvpString NVP string
     * @param bool   $isShortcut    If the operation is done in shortcut mode (not in mark)
     *
     * @return string NVP string
     */
    protected function processProductsSoldList($cartNvpString, $isShortcut)
    {
        if ($isShortcut) {
            $rows = $this->facade->getProductsSoldFromSession();
        } else {
            $rows = $this->facade->getProductsSoldFromDatabase();
        }

        $i = null;
        foreach ($rows as $i => $row) {

            $amount = urlencode(
                PaypalApiManager::convertFloatToNvpFormat($row->unitPrice)
            );
            $name = urlencode(trim($row->name));
            $quantity = urlencode($row->quantity);

            $cartNvpString .= "L_PAYMENTREQUEST_0_NAME$i=$name&";
            $cartNvpString .= "L_PAYMENTREQUEST_0_AMT$i=$amount&";
            $cartNvpString .= "L_PAYMENTREQUEST_0_QTY$i=$quantity&";
            if ($row->smallDescription !== null) {
                $smallDescription = urlencode($row->smallDescription);
                $cartNvpString .= "L_PAYMENTREQUEST_0_DESC$i=$smallDescription&";
            }
        }

        $cartNvpString = $this->processDiscountIntoCartElement($cartNvpString, $i);

        return $cartNvpString;
    }

    /**
     * Process discount to cart element so it can be displayed on paypal page
     *
     * @param string $cartNvpString NVP string to complete
     * @param int    $nbCartElement Nb item in cart
     *
     * @return string NVP string updated
     */
    protected function processDiscountIntoCartElement($cartNvpString, $nbCartElement)
    {
        $nbCartElement++;
        $discount = urlencode(
            PaypalApiManager::convertFloatToNvpFormat($this->facade->getDiscountAmount())
        );
        if ($discount > 0) {
            $cartNvpString .= "L_PAYMENTREQUEST_0_NAME$nbCartElement=Remise&";
            $cartNvpString .= "L_PAYMENTREQUEST_0_AMT$nbCartElement=-$discount&";
            $cartNvpString .= "L_PAYMENTREQUEST_0_QTY$nbCartElement=1&";

            return $cartNvpString;
        }

        return $cartNvpString;
    }

}