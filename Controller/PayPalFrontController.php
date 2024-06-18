<?php

namespace PayPal\Controller;

use OpenApi\Controller\Front\CheckoutController;
use PayPal\Model\Base\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
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
        $lang = $request->getSession()->getLang();

        $templateData['intent'] = "capture";
        $templateData['planified_payment_id'] = null;

        if (!empty($paymentOptions) && 'paypal' !== $paymentType = $paymentOptions['code']) {
            $planifiedPaymentId = explode('_', $paymentType)[1];


            $plan =  PaypalPlanifiedPaymentQuery::create()->findPk($planifiedPaymentId);
            $plan->setLocale($lang->getLocale());

            $templateData['intent'] = "subscription";
            $templateData['planified_payment_id'] = $planifiedPaymentId;
            $templateData['plan_title'] = $plan->getTitle();
            $templateData['plan_description'] = $plan->getDescription();
            $templateData['plan_frequency'] = $plan->getFrequency();
            $templateData['plan_frequency_interval'] = $plan->getFrequencyInterval();
            $templateData['plan_cycle'] = $plan->getCycle();
        }

        $templateData['order_id'] = $request->get('order_id');

        return $this->render("paypal/paypal-payment", $templateData);
    }
}