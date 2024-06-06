<?php

namespace PayPal\Controller;

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

        $templateData['order_id'] = $request->get('order_id');

        return $this->render("paypal/paypal-payment", $templateData);
    }
}