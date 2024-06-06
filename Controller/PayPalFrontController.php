<?php

namespace PayPal\Controller;

use OpenApi\Controller\Front\CheckoutController;
use PayPal\Service\Base\PayPalBaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;

#[Route("/order/paypal", name: "paypal_front_")]
class PayPalFrontController extends BaseFrontController
{
    #[Route("/pay", name: "pay", methods: "GET")]
    public function showPayPalPaymentPage(Request $request)
    {
        $templateData = [];
        $templateData['paypal_mode'] = PayPalBaseService::getMode();
        $templateData['paypal_merchant_id'] = PayPalBaseService::getMerchantId();
        $templateData['paypal_client_id'] = PayPalBaseService::getLogin();
        $paymentOptions = $request->getSession()->get(CheckoutController::PAYMENT_MODULE_OPTION_CHOICES_SESSION_KEY);

        $templateData['intent'] = "capture";
        $templateData['planified_payment_id'] = null;

        if (!empty($paymentOptions)) {
            foreach ($paymentOptions as $group => $values) {
                if ($group !== 'paypal_type') {
                    continue;
                }

                if ('paypal' !== $paymentType = array_pop($values)) {
                    $planifiedPaymentId = explode('_', $paymentType)[1];

                    $templateData['intent'] = "subscription";
                    $templateData['planified_payment_id'] = $planifiedPaymentId;
                }
            }
        }

        $templateData['order_id'] = $request->get('order_id');

        return $this->render("paypal/paypal-payment", $templateData);
    }
}