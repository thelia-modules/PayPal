<?php

namespace PayPal\Controller;

use PayPal\Event\PayPalOrderEvent;
use PayPal\Model\PaypalOrder;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use PayPal\Service\PayPalApiService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;
use Thelia\Model\Base\AddressQuery;
use Thelia\Model\Base\ModuleQuery;
use Thelia\Model\Base\ProductQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\LangQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatusQuery;
use Thelia\Module\Exception\DeliveryException;

#[Route("/paypal/api", name: "paypal_api_")]
class PayPalApiController extends BaseFrontController
{

    #[Route("/pay", name: "pay", methods: "POST")]
    public function createOrder(Request $request, PayPalApiService $payPalApiService, EventDispatcherInterface $eventDispatcher)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $order = $this->createTheliaOrder($request, $eventDispatcher, $data);

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

            $paypalOrder = new PaypalOrder();
            $paypalOrder
                ->setId($order->getId())
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

            $paypalOrderId = $data['paypal_order_id'];

            $response = $payPalApiService->sendPostResquest(
                null,
                PayPal::getBaseUrl().PayPal::PAYPAL_API_CREATE_ORDER_URL.'/'.$paypalOrderId.'/capture'
            );

            $responseContent = $response->getContent();
            return new JsonResponse($responseContent);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return new JsonResponse(json_encode(['error' => $exception->getMessage()]), $exception->getCode());
        }
    }

    #[Route("/planified-payment", name: "planified-payment", methods: "POST")]
    public function createPlan(Request $request, EventDispatcherInterface $eventDispatcher, PayPalApiService $payPalApiService)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $lang = $request->getSession()->getLang();

            $order = $this->createTheliaOrder($request, $eventDispatcher, $data);

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

    private function createTheliaOrder(Request $request, EventDispatcherInterface $eventDispatcher, $data)
    {
        $session = $request->getSession();

        $translator = Translator::getInstance();

        $cart = $session->getSessionCart($eventDispatcher);
        $currency = $cart?->getCurrency();
        $lang = $session->getLang();

        $deliveryAddress = AddressQuery::create()->findPk($data['delivery_address_id']);
        $invoiceAddress = AddressQuery::create()->findPk($data['invoice_address_id']);
        $paymentModule = ModuleQuery::create()->findPk(PayPal::getModuleId());
        $deliveryModule = ModuleQuery::create()->findPk($data['delivery_module_id']);

        $order = new Order();
        $order
            ->setCustomerId($cart?->getCustomerId())
            ->setCurrencyId($currency->getId())
            ->setCurrencyRate($currency->getRate())
            ->setStatusId(OrderStatusQuery::getNotPaidStatus()?->getId())
            ->setLangId($lang->getDefaultLanguage()->getId())
            ->setChoosenDeliveryAddress($deliveryAddress)
            ->setChoosenInvoiceAddress($invoiceAddress)
            ->setPaymentModuleId($paymentModule->getId())
            ->setDeliveryModuleId($deliveryModule->getId())
        ;

        $orderEvent = new OrderEvent($order);

        $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);
        $deliveryPostageEvent = new DeliveryPostageEvent($moduleInstance, $cart, $deliveryAddress);

        $eventDispatcher->dispatch(
            $deliveryPostageEvent,
            TheliaEvents::MODULE_DELIVERY_GET_POSTAGE
        );

        if (!$deliveryPostageEvent->isValidModule() || null === $deliveryPostageEvent->getPostage()) {
            throw new DeliveryException(
                $translator->trans('The delivery module is not valid.', [], PayPal::DOMAIN_NAME)
            );
        }

        $postage = $deliveryPostageEvent->getPostage();

        $orderEvent->setPostage($postage?->getAmount());
        $orderEvent->setPostageTax($postage?->getAmountTax());
        $orderEvent->setPostageTaxRuleTitle($postage?->getTaxRuleTitle());
        $orderEvent->setDeliveryAddress($deliveryAddress->getId());
        $orderEvent->setInvoiceAddress($invoiceAddress->getId());
        $orderEvent->setDeliveryModule($deliveryModule->getId());
        $orderEvent->setPaymentModule($paymentModule->getId());

        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_DELIVERY_ADDRESS);
        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_INVOICE_ADDRESS);
        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_POSTAGE);
        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_DELIVERY_MODULE);
        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_PAYMENT_MODULE);

        $orderManualEvent = new OrderManualEvent(
            $orderEvent->getOrder(),
            $orderEvent->getOrder()->getCurrency(),
            $orderEvent->getOrder()->getLang(),
            $cart,
            $cart?->getCustomer()
        );

        $eventDispatcher->dispatch($orderManualEvent, TheliaEvents::ORDER_CREATE_MANUAL);

        return $orderManualEvent->getPlacedOrder();
    }

}