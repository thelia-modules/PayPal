<?php

namespace PayPal\Controller;

use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\CartQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\OrderQuery;

#[Route("/order/paypal", name: "paypal_front_")]
class PayPalFrontController extends BaseFrontController
{
    /**
     * Session key historically populated by `OpenApi\Controller\Front\CheckoutController`
     * to remember the customer's payment module option selection. Kept as a
     * literal so PayPal stays backwards compatible with sessions seeded by the
     * legacy module while we migrate the checkout flow to API Platform 4.3.
     */
    private const PAYMENT_MODULE_OPTION_CHOICES_SESSION_KEY = 'payment_module_option_choices';

    #[Route("/pay", name: "pay")]
    public function showPayPalPaymentPage(Request $request)
    {
        $templateData = [];
        $templateData['paypal_mode'] = PayPalBaseService::getMode();
        $templateData['paypal_merchant_id'] = PayPalBaseService::getMerchantId();
        $templateData['paypal_client_id'] = PayPalBaseService::getLogin();
        $paymentOptions = $request->getSession()->get(self::PAYMENT_MODULE_OPTION_CHOICES_SESSION_KEY);
        $lang = $request->getSession()->getLang();

        $templateData['intent'] = "capture";
        $templateData['planified_payment_id'] = null;
        $templateData['currency'] = CurrencyQuery::create()->findOneByByDefault(1)->getCode();

        $templateData['infos'] = PayPal::getInfo($lang->getLocale());

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

        $orderId = $request->query->get('order_id');

        $templateData['order_id'] = $orderId;

        if ($orderId) {
            $order = OrderQuery::create()->filterByCustomerId($this->getSession()->getCustomerUser()->getId())->findPk($request->get('order_id'));
            $cart = CartQuery::create()->findOneById($order?->getCartId());
            $this->getRequest()->getSession()->setSessionCart($cart);
        }

        return $this->render("paypal-payment", $templateData);
    }
}
