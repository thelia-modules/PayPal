<?php

namespace PayPal\Controller;

use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\Model\PaypalOrderQuery;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use PayPal\Service\OrderOwnerGuard;
use PayPal\Service\PayPalApiService;
use PayPal\Service\PayPalDeferredCapture;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderPayTotalEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;
use Thelia\Log\Tlog;
use Thelia\Model\Base\OrderStatusQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;

#[Route("/paypal/api", name: "paypal_api_")]
class PayPalApiController extends BaseFrontController
{

    #[Route("/pay", name: "pay", methods: "POST")]
    public function createPaypalOrder(Request $request, PayPalApiService $payPalApiService, EventDispatcherInterface $eventDispatcher, OrderOwnerGuard $orderOwnerGuard, PayPalDeferredCapture $deferredCapture)
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            return $this->errorResponse('Invalid request body', 400);
        }

        // An order already paid, or authorized, waits for no payment, whatever the way it is paid.
        $order = $this->findOrder($data);

        if (null !== $order && $orderOwnerGuard->isOwnedBy($order, $this->getSession()->getCustomerUser()) && $order->isPaymentSecured()) {
            return $this->errorResponse('This order is already paid.', 409);
        }

        if (array_key_exists('planified_payment_id', $data) && !empty($data['planified_payment_id'])) {
            return $this->createPlan($request, $payPalApiService, $data, $orderOwnerGuard);
        }

        return $this->createOrder($request, $payPalApiService, $eventDispatcher, $data, $orderOwnerGuard, $deferredCapture);
    }


    public function createOrder(Request $request, PayPalApiService $payPalApiService, EventDispatcherInterface $eventDispatcher, $data, OrderOwnerGuard $orderOwnerGuard, PayPalDeferredCapture $deferredCapture)
    {
        try {
            $order = $this->findOrder($data);

            if (!$orderOwnerGuard->isOwnedBy($order, $this->getSession()->getCustomerUser())) {
                return new JsonResponse(json_encode(['error' => 'Order not found']), 404);
            }

            // An order already paid, or authorized, waits for no payment.
            if ($order->isPaymentSecured()) {
                return $this->errorResponse('This order is already paid.', 409);
            }

            $currency = CurrencyQuery::create()->findPk($order->getCurrencyId());

            $orderPayEvent = new OrderPayTotalEvent($order);
            $orderPayEvent
                ->setTax(0)
                ->setIncludePostage(true)
                ->setIncludeDiscount(true);

            $eventDispatcher->dispatch($orderPayEvent, TheliaEvents::ORDER_PAY_GET_TOTAL);

            $body = [
                'intent' => $intent = PayPal::orderIntent(),
                'purchase_units' => [
                    [
                        'reference_id' => $order->getRef(),
                        'amount' => [
                            'currency_code' => $currency?->getCode(),
                            'value' => round($orderPayEvent->getTotal(), 2)
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

            $response = $payPalApiService->sendPostResquest($body, PayPal::getBaseUrl() . PayPal::PAYPAL_API_CREATE_ORDER_URL);
            $responseContent = $response->getContent();
            $responseInfo = json_decode($responseContent, true, 512, \JSON_THROW_ON_ERROR);

            // Without the id of the order PayPal created, the buyer could never come back to it.
            if (!\is_array($responseInfo) || '' === (string) ($responseInfo['id'] ?? '')) {
                Tlog::getInstance()->error(sprintf('PayPal answered the creation of the payment of order %s without its id.', $order->getRef()));

                return $this->errorResponse('The payment could not be created: please try again.', 502);
            }

            $paypalOrder = PaypalOrderQuery::create()
                ->filterById($order->getId())
                ->findOneOrCreate();

            $paypalOrder->setPaymentId($responseInfo['id']);
            // The buyer comes back to this order: it is then authorized or captured as created.
            $deferredCapture->rememberIntent($this->getSession(), (string) $responseInfo['id'], $intent);

            $paypalOrder
                ->setPlanifiedTitle("")
                ->setPlanifiedFrequency("")
                ->setPlanifiedFrequencyInterval(0)
                ->setPlanifiedCycle(0)
                ->setPlanifiedMinAmount(0)
                ->setPlanifiedMaxAmount(0);

            $payPalOrderEvent = new PayPalOrderEvent($paypalOrder);
            $eventDispatcher->dispatch($payPalOrderEvent, PayPalEvents::PAYPAL_ORDER_CREATE);

            $this->getSession()->clearSessionCart($eventDispatcher);

            return new JsonResponse($responseContent);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return $this->errorResponse($exception->getMessage(), $this->errorStatus($exception));
        }

    }

    #[Route("/capture", name: "capture", methods: "POST")]
    public function captureOrder(Request $request, PayPalApiService $payPalApiService, EventDispatcherInterface $dispatcher, OrderOwnerGuard $orderOwnerGuard, PayPalDeferredCapture $deferredCapture)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $order = \is_array($data) ? $this->findOrder($data) : null;

            if (!$orderOwnerGuard->isOwnedBy($order, $this->getSession()->getCustomerUser())
                || null === $paypalOrder = PaypalOrderQuery::create()->findPk($order->getId())) {
                return $this->errorResponse('Order not found', 404);
            }

            // Authorizing first, the approved order is authorized, not captured: the authorization is
            // written to Thelia's payment journal, which holds the order for its capture. Decided on
            // the intent the PayPal order was created with, which the capture mode may have left since.
            if ('AUTHORIZE' === $deferredCapture->intentOf($this->getSession(), (string) $paypalOrder->getPaymentId())) {
                try {
                    $answer = $deferredCapture->authorize($paypalOrder->getOrder(), (string) $paypalOrder->getPaymentId());
                } catch (PaymentRefusedException $refused) {
                    Tlog::getInstance()->error(sprintf('PayPal refused the authorization of order %s: %s', $paypalOrder->getOrder()->getRef(), $refused->getMessage()));

                    return $this->errorResponse('PayPal refused the payment.', 422);
                } catch (\Throwable $failure) {
                    // The cause is in the log, written by the service: the buyer is not shown the
                    // words of the journal or of the database.
                    Tlog::getInstance()->error(sprintf('The authorization of order %s failed: %s', $paypalOrder->getOrder()->getRef(), $failure->getMessage()));

                    return $this->errorResponse('The payment could not be authorized: please try again.', 502);
                }

                $deferredCapture->forgetIntent($this->getSession(), (string) $paypalOrder->getPaymentId());

                return new JsonResponse(json_encode($answer, \JSON_THROW_ON_ERROR));
            }

            $response = $payPalApiService->sendPostResquest(
                null,
                PayPal::getBaseUrl() . PayPal::PAYPAL_API_CREATE_ORDER_URL . '/' . $paypalOrder->getPaymentId() . '/capture'
            );

            $responseContent = $response->getContent();
            $status = json_decode($responseContent, true)['status'];
            if ("COMPLETED" === $status) {
                // Kept otherwise: a retry of this order is captured as it was created.
                $deferredCapture->forgetIntent($this->getSession(), (string) $paypalOrder->getPaymentId());
                $event = new OrderEvent($paypalOrder->getOrder());
                $event->setStatus(OrderStatusQuery::create()->filterByCode(OrderStatus::CODE_PAID)->findOne()?->getId());
                $dispatcher->dispatch($event, TheliaEvents::ORDER_UPDATE_STATUS);
            }
            return new JsonResponse($responseContent);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return $this->errorResponse($exception->getMessage(), $this->errorStatus($exception));
        }
    }

    public function createPlan(Request $request, PayPalApiService $payPalApiService, $data, OrderOwnerGuard $orderOwnerGuard)
    {
        try {
            $lang = $request->getSession()->getLang();

            $order = $this->findOrder($data);

            if (!$orderOwnerGuard->isOwnedBy($order, $this->getSession()->getCustomerUser())) {
                return new JsonResponse(json_encode(['error' => 'Order not found']), 404);
            }

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
            return new JsonResponse($responseContent);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
            return $this->errorResponse($exception->getMessage(), $this->errorStatus($exception));
        }

    }

    private function findOrder(array $data): ?Order
    {
        $orderId = $data['order_id'] ?? null;

        return is_numeric($orderId) ? OrderQuery::create()->findPk((int) $orderId) : null;
    }

    /**
     * An exception code is not an HTTP status (most are 0): only a real error status is kept.
     */
    private function errorStatus(\Throwable $exception): int
    {
        $code = $exception->getCode();

        return \is_int($code) && $code >= 400 && $code <= 599 ? $code : 500;
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse(json_encode(['error' => $message]), $status);
    }
}
