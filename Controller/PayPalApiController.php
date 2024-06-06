<?php

namespace PayPal\Controller;

use PayPal\Model\PaypalOrderQuery;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use PayPal\Service\PayPalApiService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Log\Tlog;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\OrderQuery;

#[Route("/paypal/api", name: "paypal_api_")]
class PayPalApiController extends BaseFrontController
{

    #[Route("/pay", name: "pay", methods: "POST")]
    public function createPaypalOrder(Request $request, PayPalApiService $payPalApiService){
        $data = json_decode($request->getContent(), true);

        if (array_key_exists('planified_payment_id', $data) && !empty($data['planified_payment_id'])) {
            return $this->createPlan($request, $payPalApiService, $data);
        }

        return $this->createOrder($request, $payPalApiService, $data);
    }


    public function createOrder(Request $request, PayPalApiService $payPalApiService, $data)
    {
        try {
            $order = OrderQuery::create()->findPk($data['order_id']);

            $currency = CurrencyQuery::create()->findPk($order->getCurrencyId());

            $body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->getRef(),
                        'amount' => [
                            'currency_code' => $currency?->getCode(),
                            'value' => $order->getTotalAmount($tax, true, true)
                        ]
                    ]
                ]
            ];

            $body['payment_source'] = [
                'paypal' => [
                    'experience_context' => [
                        'landing_page' => 'NO_PREFERENCE',
                        'user_action' => 'PAY_NOW',
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'return_url' => $data['return_url'],
                        'cancel_url' => $data['cancel_url'],
                    ]
                ]
            ];

            if (!empty($data['use_card']) && true === (bool)$data['use_card']) {
                $body['payment_source'] = [
                    'card' => [
                        'card_name' => $data['name'],
                        'card_number' => $data['number'],
                        'card_security_code' => $data['security_code'],
                        'card_expiry' => $data['expiry']
                    ]
                ];
            }

            $response = $payPalApiService->sendPostResquest($body, PayPal::getBaseUrl().PayPal::PAYPAL_API_CREATE_ORDER_URL);
            $responseContent = $response->getContent();
            $responseInfo = json_decode($responseContent, true);

            $paypalOrder = PaypalOrderQuery::create()
                ->filterById($order->getId())
                ->findOneOrCreate();

            $paypalOrder
                ->setPaymentId($responseInfo['id'])
                ->save();

            return new JsonResponse($responseContent);
        }catch (\Exception $exception){
            Tlog::getInstance()->error($exception->getMessage());
            return new JsonResponse(json_encode(['error' => $exception->getMessage()]), $exception->getCode());
        }

    }

    #[Route("/capture", name: "capture", methods: "POST")]
    public function captureOrder(Request $request, PayPalApiService $payPalApiService)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $paypalOrder = PaypalOrderQuery::create()->findPk($data['order_id']);

            $response = $payPalApiService->sendPostResquest(
                null,
                PayPal::getBaseUrl().PayPal::PAYPAL_API_CREATE_ORDER_URL.'/'.$paypalOrder->getPaymentId().'/capture'
            );

            $responseContent = $response->getContent();
            return new JsonResponse($responseContent);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return new JsonResponse(json_encode(['error' => $exception->getMessage()]), $exception->getCode());
        }
    }

    public function createPlan(Request $request, PayPalApiService $payPalApiService, $data)
    {
        try {
            $lang = $request->getSession()->getLang();

            $order = OrderQuery::create()->findPk($data['order_id']);

            $planifiedPayment = PaypalPlanifiedPaymentQuery::create()->findPk($data["planified_payment_id"]);
            $planifiedPayment->setLocale($lang?->getLocale());

            $totalAmount = $order->getTotalAmount();
            $cycleAmount = round($totalAmount / $planifiedPayment->getCycle(), 2);

            $currency = CurrencyQuery::create()->findPk($order->getCurrencyId());

            $body = [
                "product_id" => $planifiedPayment->getPaypalId(),
                "name" => $planifiedPayment->getTitle(),
                "description" => $planifiedPayment->getDescription(),
                "billing_cycles" => [[
                    "tenure_type" => 'REGULAR',
                    "sequence" => 1,
                    "total_cycles" => $planifiedPayment->getCycle(),
                    "frequency" => [
                        "interval_unit" => $planifiedPayment->getFrequency(),
                        "interval_count" => $planifiedPayment->getFrequencyInterval(),
                    ],
                    "pricing_scheme" => [
                        "fixed_price" => [
                            "currency_code" => $currency->getCode(),
                            "value" => $cycleAmount
                        ]
                    ]
                ]],
                "payment_preferences" => [
                    "setup_fee" => [
                        "currency_code" => $currency->getCode(),
                        "value" => 0,
                    ]
                ]
            ];

            $response = $payPalApiService->sendPostResquest($body, PayPal::getBaseUrl() . PayPal::PAYPAL_API_CREATE_PLAN_URL);
            $responseContent = $response->getContent();
            $responseInfo = json_decode($responseContent, true);
            return new JsonResponse($responseInfo);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return new JsonResponse(json_encode(['error' => $exception->getMessage()]), $exception->getCode());
        }

    }

}