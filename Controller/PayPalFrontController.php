<?php

namespace PayPal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;

#[Route("/order/paypal", name: "paypal_front_")]
class PayPalFrontController extends BaseFrontController
{
    #[Route("/pay", name: "pay", methods: "GET")]
    public function showPayPalPaymentPage(Request $request)
    {
        return $this->render("paypal/paypal-payment", ['order_id'=> $request->get("order_id")]);
    }
}