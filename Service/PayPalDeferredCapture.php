<?php

declare(strict_types=1);

namespace PayPal\Service;

use PayPal\PayPal;
use PayPal\Service\Capture\PayPalAnswer;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Thelia\Domain\Payment\DTO\PaymentOperationResult;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;
use Thelia\Domain\Payment\Service\CurrencyMinorUnit;
use Thelia\Domain\Payment\Service\PaymentTransactionRecorder;
use Thelia\Model\Order;
use Thelia\Model\OrderPaymentTransaction;
use Thelia\Model\OrderPaymentTransactionQuery;

/**
 * PayPal authorizes first and Thelia captures (Orders API and Payments API v2).
 *
 * The checkout asks PayPal to authorize the order the buyer approved; the authorization is written
 * to Thelia's payment journal, which holds the order for its capture. Thelia's capture takes all or
 * part of it, leaving the rest open (final_capture false, PayPal allows several captures); its
 * release voids it. Each call carries a PayPal-Request-Id, so that a retried call is answered with
 * the outcome of the first one.
 *
 * An answer of PayPal in the 4xx range is a refusal; a call without answer, or a 5xx, leaves the
 * outcome unknown and is left to surface: Thelia then keeps the line pending.
 */
#[Autoconfigure(public: true)]
final readonly class PayPalDeferredCapture
{
    private const AUTHORIZATIONS_URL = '/v2/payments/authorizations/';

    public function __construct(
        private PayPalApiService $api,
        private PaymentTransactionRecorder $recorder,
    ) {
    }

    /**
     * Asks PayPal to authorize the order the buyer approved, and writes the authorization.
     *
     * @return array<string, mixed> PayPal's answer, for the checkout to read its status
     */
    public function authorize(Order $order, string $payPalOrderId): array
    {
        $answer = $this->call(
            PayPal::getBaseUrl().PayPal::PAYPAL_API_CREATE_ORDER_URL.'/'.rawurlencode($payPalOrderId).'/authorize',
            new \stdClass(),
            'thelia-authorize-'.$order->getId(),
        );

        $authorization = PayPalAnswer::authorizationOf($answer);

        if (null !== $authorization) {
            $this->recorder->recordAuthorization($order, $authorization->amount, $authorization->id, moduleCode: PayPal::getModuleCode());
        }

        return $answer;
    }

    public function capture(Order $order, float $amount, OrderPaymentTransaction $transaction): PaymentOperationResult
    {
        $currencyCode = (string) $order->getCurrency()->getCode();

        $answer = $this->call(
            PayPal::getBaseUrl().self::AUTHORIZATIONS_URL.rawurlencode($this->authorizationOf($order)).'/capture',
            [
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => number_format($amount, CurrencyMinorUnit::decimalsOf($currencyCode), '.', ''),
                ],
                'final_capture' => false,
            ],
            'thelia-capture-'.$transaction->getId(),
        );

        return PayPalAnswer::captureResult($answer);
    }

    public function release(Order $order, OrderPaymentTransaction $transaction): PaymentOperationResult
    {
        $authorizationId = $this->authorizationOf($order);

        $this->call(
            PayPal::getBaseUrl().self::AUTHORIZATIONS_URL.rawurlencode($authorizationId).'/void',
            new \stdClass(),
            'thelia-void-'.$transaction->getId(),
        );

        return PaymentOperationResult::succeeded($authorizationId);
    }

    private function authorizationOf(Order $order): string
    {
        $reference = OrderPaymentTransactionQuery::create()->findLatestSucceededAuthorization((int) $order->getId())?->getPspReference();

        if (null === $reference || '' === $reference) {
            throw new PaymentRefusedException(\sprintf('Order %s holds no PayPal authorization.', (string) $order->getRef()));
        }

        return $reference;
    }

    /**
     * @return array<string, mixed> the body PayPal answered, empty for a 204
     *
     * @throws PaymentRefusedException when PayPal refused the call
     * @throws \RuntimeException       when PayPal did not answer the call
     */
    private function call(string $url, array|\stdClass $body, string $requestId): array
    {
        /** @var ResponseInterface $response */
        $response = $this->api->sendPostResquest($body, $url, $requestId);
        $code = $response->getStatusCode();
        $answer = json_decode($response->getContent(false) ?: '{}', true);
        $answer = \is_array($answer) ? $answer : [];

        if ($code >= 500) {
            throw new \RuntimeException(\sprintf('PayPal did not process the call (HTTP %d).', $code));
        }

        if ($code >= 400) {
            throw new PaymentRefusedException(PayPalAnswer::errorOf($answer));
        }

        return $answer;
    }
}
