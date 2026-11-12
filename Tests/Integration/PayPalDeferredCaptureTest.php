<?php

declare(strict_types=1);

namespace PayPal\Tests\Integration;

use PayPal\PayPal;
use PayPal\Service\PayPalApiService;
use PayPal\Service\PayPalDeferredCapture;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Domain\Payment\Enum\PaymentTransactionState;
use Thelia\Domain\Payment\Exception\PaymentException;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;
use Thelia\Domain\Payment\Service\PaymentTransactionRecorder;
use Thelia\Domain\Payment\Service\PaymentTransactionTotalsReader;
use Thelia\Model\Module;
use Thelia\Model\ModuleConfigQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderStatus;
use Thelia\Module\BaseModule;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * PayPal authorizes first and Thelia captures: the authorization the checkout obtains goes to the
 * payment journal, and the capture and the release Thelia asks for go to PayPal's Payments API.
 * PayPal is played by a mock HTTP client. Runs with the shop's own configuration:
 *     vendor/bin/phpunit -c phpunit.xml.dist local/modules/PayPal/tests/Integration
 */
final class PayPalDeferredCaptureTest extends ActionIntegrationTestCase
{
    private PaymentTransactionRecorder $recorder;

    /** @var list<array{method: string, url: string, options: array<string, mixed>}> */
    private array $requests = [];

    protected function setUp(): void
    {
        parent::setUp();

        (new \ReflectionProperty(BaseModule::class, 'moduleIds'))->setValue(null, []);
        $this->registerTheModule();
        PayPal::setConfigValue('sandbox', 1);
        PayPal::setConfigValue('sandbox_login', 'client-id');
        PayPal::setConfigValue('sandbox_password', 'client-secret');
        PayPal::setConfigValue('capture_mode', 'authorize');

        $this->recorder = $this->getService(PaymentTransactionRecorder::class);
    }

    public function testTheModuleAuthorizesFirstOnlyWhenConfiguredTo(): void
    {
        self::assertTrue((new PayPal())->supportsDeferredCapture());

        PayPal::setConfigValue('capture_mode', 'capture');

        self::assertFalse((new PayPal())->supportsDeferredCapture());
    }

    public function testTheCheckoutAsksPayPalToAuthorizeOnlyWhenConfiguredTo(): void
    {
        self::assertSame('AUTHORIZE', PayPal::orderIntent());

        PayPal::setConfigValue('capture_mode', 'capture');

        self::assertSame('CAPTURE', PayPal::orderIntent());
    }

    public function testTheAuthorizationTheCheckoutObtainsIsWrittenAndHoldsTheOrder(): void
    {
        $order = $this->order();

        $answer = $this->service([$this->json(201, [
            'id' => 'ORDER-1',
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['authorizations' => [['id' => 'AUTH-9', 'status' => 'CREATED', 'amount' => ['currency_code' => 'EUR', 'value' => '120.00']]]]]],
        ])])->authorize($order, 'ORDER-1');

        self::assertSame('COMPLETED', $answer['status']);
        self::assertSame('POST', $this->requests[1]['method']);
        self::assertStringEndsWith('/v2/checkout/orders/ORDER-1/authorize', $this->requests[1]['url']);
        self::assertContains('PayPal-Request-Id: thelia-authorize-'.$order->getId().'-ORDER-1', $this->requests[1]['options']['headers'], 'Paying again opens another PayPal order, which PayPal must not answer with the first.');
        $totals = $this->getService(PaymentTransactionTotalsReader::class)->forOrder((int) $order->getId());
        self::assertSame('120.000000', $totals->authorized);
        self::assertSame(OrderStatus::CODE_AWAITING_CAPTURE, $this->statusOf($order));
    }

    public function testACaptureLeavesTheRestOfTheAuthorizationOpen(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordCapture($order, 40, null, PaymentTransactionState::PENDING);

        $result = $this->service([$this->json(201, ['id' => 'CAP-3', 'status' => 'COMPLETED'])])->capture($order, 40.0, $line);

        self::assertSame(PaymentTransactionState::SUCCEEDED, $result->state);
        self::assertSame('CAP-3', $result->pspReference);
        self::assertStringEndsWith('/v2/payments/authorizations/AUTH-9/capture', $this->requests[1]['url']);
        self::assertSame(
            ['amount' => ['currency_code' => 'EUR', 'value' => '40.00'], 'final_capture' => false],
            json_decode((string) $this->requests[1]['options']['body'], true),
        );
        self::assertContains('PayPal-Request-Id: thelia-capture-'.$line->getId(), $this->requests[1]['options']['headers']);
    }

    public function testACapturePayPalRefusesIsARefusal(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordCapture($order, 40, null, PaymentTransactionState::PENDING);

        try {
            $this->service([$this->json(422, ['name' => 'UNPROCESSABLE_ENTITY', 'details' => [['issue' => 'AUTHORIZATION_EXPIRED', 'description' => 'Authorization has expired.']]])])->capture($order, 40.0, $line);
            self::fail('The refusal of PayPal must reach Thelia.');
        } catch (PaymentRefusedException $refusal) {
            self::assertStringContainsString('AUTHORIZATION_EXPIRED', $refusal->getMessage());
            self::assertSame('AUTHORIZATION_EXPIRED', $refusal->getProviderCode(), 'The issue PayPal named is written on the journal line as its code.');
        }
    }

    public function testACaptureWithoutAnswerFromPayPalIsLeftToSurface(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordCapture($order, 40, null, PaymentTransactionState::PENDING);

        try {
            $this->service([$this->json(503, ['name' => 'SERVICE_UNAVAILABLE'])])->capture($order, 40.0, $line);
            self::fail('PayPal did not answer: no outcome can be reported.');
        } catch (PaymentRefusedException) {
            self::fail('PayPal did not answer: it did not refuse either.');
        } catch (\RuntimeException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testAnAcceptanceInWordsTheModuleCannotReadIsNoOutcome(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordCapture($order, 40, null, PaymentTransactionState::PENDING);

        try {
            $this->service([new MockResponse('<html>gateway</html>', ['http_code' => 201])])->capture($order, 40.0, $line);
            self::fail('An answer the module cannot read is no outcome.');
        } catch (PaymentRefusedException) {
            self::fail('PayPal may have taken the money: it is not a refusal.');
        } catch (\RuntimeException $unknown) {
            self::assertStringContainsString('cannot read', $unknown->getMessage());
        }
    }

    public function testARefusalInWordsTheModuleCannotReadIsStillARefusal(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordCapture($order, 40, null, PaymentTransactionState::PENDING);

        $this->expectException(PaymentRefusedException::class);

        $this->service([new MockResponse('<html>bad request</html>', ['http_code' => 400])])->capture($order, 40.0, $line);
    }

    public function testAnAuthorizationTheJournalCannotWriteFailsTheCheckout(): void
    {
        // PayPal holds the money: the checkout must not tell the buyer the payment went through.
        $order = $this->order();

        $id = str_repeat('A', 101);

        try {
            $this->service([
                $this->json(201, [
                    'id' => 'ORDER-2',
                    'status' => 'COMPLETED',
                    'purchase_units' => [['payments' => ['authorizations' => [['id' => $id, 'status' => 'CREATED', 'amount' => ['currency_code' => 'EUR', 'value' => '120.00']]]]]],
                ]),
                $this->json(200, ['access_token' => 'token']),
                new MockResponse('', ['http_code' => 204]),
            ])->authorize($order, 'ORDER-2');
            self::fail('The checkout fails.');
        } catch (PaymentException) {
            // The authorization Thelia could not write is voided: paying again holds nothing twice.
            self::assertStringEndsWith('/v2/payments/authorizations/'.$id.'/void', end($this->requests)['url']);
        }
    }

    public function testAnOrderAlreadyAuthorizedIsNotAuthorizedAgain(): void
    {
        // A second authorization would hold the buyer's money twice and open a second capture.
        $order = $this->authorizedOrder();

        try {
            $this->service([])->authorize($order, 'ORDER-3');
            self::fail('The second authorization is refused.');
        } catch (PaymentRefusedException) {
            self::assertSame([], $this->requests, 'PayPal is not asked.');
        }

        self::assertSame('120.000000', $this->getService(PaymentTransactionTotalsReader::class)->forOrder((int) $order->getId())->authorized);
    }

    public function testAnAuthorizationInAnotherCurrencyIsNotThisOrdersPayment(): void
    {
        $order = $this->order();

        try {
            $this->service([$this->json(201, [
                'id' => 'ORDER-4',
                'status' => 'COMPLETED',
                'purchase_units' => [['payments' => ['authorizations' => [['id' => 'AUTH-USD', 'status' => 'CREATED', 'amount' => ['currency_code' => 'USD', 'value' => '120.00']]]]]],
            ])])->authorize($order, 'ORDER-4');
            self::fail('An authorization in another currency is refused.');
        } catch (PaymentRefusedException) {
            $this->addToAssertionCount(1);
        }

        self::assertSame('0.000000', $this->getService(PaymentTransactionTotalsReader::class)->forOrder((int) $order->getId())->authorized);
    }

    public function testAnAuthorizationTheJournalAlreadyHoldsIsNeverVoided(): void
    {
        // A second click wrote the authorization while this request asked PayPal; this one cannot
        // write it (another amount under the same reference): the authorization stays at PayPal.
        $order = $this->order();
        $answer = $this->json(201, [
            'id' => 'ORDER-6',
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['authorizations' => [['id' => 'AUTH-KNOWN', 'status' => 'CREATED', 'amount' => ['currency_code' => 'EUR', 'value' => '120.00']]]]]],
        ]);
        $responses = [$this->json(200, ['access_token' => 'token']), $answer, $this->json(200, ['access_token' => 'token']), new MockResponse('', ['http_code' => 204])];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses, $order): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            if (str_ends_with($url, '/authorize')) {
                $this->recorder->recordAuthorization($order, 50, 'AUTH-KNOWN', moduleCode: PayPal::getModuleCode());
            }

            return array_shift($responses);
        });

        try {
            (new PayPalDeferredCapture(new PayPalApiService($client), $this->recorder))->authorize($order, 'ORDER-6');
            self::fail('The journal refuses the answer.');
        } catch (PaymentException) {
            self::assertSame([], array_filter($this->requests, static fn (array $request): bool => str_ends_with($request['url'], '/void')), 'The authorization the journal holds is not voided.');
        }
    }

    public function testAPayPalOrderIsAuthorizedOrCapturedAsItWasCreated(): void
    {
        $service = $this->service([]);
        $session = new Session(new MockArraySessionStorage());
        $service->rememberIntent($session, 'ORDER-5', 'AUTHORIZE');
        PayPal::setConfigValue('capture_mode', 'capture');

        self::assertSame('AUTHORIZE', $service->intentOf($session, 'ORDER-5'), 'The capture mode changed during the checkout.');
        self::assertSame('CAPTURE', $service->intentOf($session, 'ORDER-UNKNOWN'), 'An order the session does not know follows the capture mode.');
        self::assertSame('CAPTURE', $service->intentOf(new Session(new MockArraySessionStorage()), 'ORDER-5'), 'Another session knows nothing of it.');

        $service->forgetIntent($session, 'ORDER-5');

        self::assertSame('CAPTURE', $service->intentOf($session, 'ORDER-5'));
        self::assertSame(0, ModuleConfigQuery::create()->filterByModuleId(PayPal::getModuleId())->filterByName('%intent%', \Propel\Runtime\ActiveQuery\Criteria::LIKE)->count(), 'Nothing is left in the module configuration.');
    }

    public function testTheReleaseVoidsTheAuthorization(): void
    {
        $order = $this->authorizedOrder();
        $line = $this->recorder->recordVoid($order, null, PaymentTransactionState::PENDING);

        $result = $this->service([new MockResponse('', ['http_code' => 204])])->release($order, $line);

        self::assertSame(PaymentTransactionState::SUCCEEDED, $result->state);
        self::assertSame('AUTH-9', $result->pspReference);
        self::assertStringEndsWith('/v2/payments/authorizations/AUTH-9/void', $this->requests[1]['url']);
    }

    public function testTheModuleReachesItsCaptureServiceFromTheContainer(): void
    {
        // The module class fetches it from the container, which only hands out public services.
        self::assertTrue(static::$kernel->getContainer()->has(PayPalDeferredCapture::class));
    }

    private function authorizedOrder(): Order
    {
        $order = $this->order();
        $this->recorder->recordAuthorization($order, 120, 'AUTH-9', moduleCode: PayPal::getModuleCode());
        $this->requests = [];

        return $order;
    }

    /**
     * @param list<MockResponse> $answers what PayPal answers after the access token
     */
    private function service(array $answers): PayPalDeferredCapture
    {
        $responses = array_merge([$this->json(200, ['access_token' => 'token'])], $answers);
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return array_shift($responses);
        });

        return new PayPalDeferredCapture(new PayPalApiService($client), $this->recorder);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function json(int $code, array $body): MockResponse
    {
        return new MockResponse(json_encode($body, \JSON_THROW_ON_ERROR), ['http_code' => $code, 'response_headers' => ['content-type' => 'application/json']]);
    }

    private function order(): Order
    {
        return $this->factory->order(null, ['postage' => 120, 'paymentModuleCode' => PayPal::getModuleCode()]);
    }

    private function statusOf(Order $order): string
    {
        $order->reload();

        return (string) $order->getOrderStatus()->getCode();
    }

    private function registerTheModule(): void
    {
        if (null !== ModuleQuery::create()->findOneByCode(PayPal::getModuleCode())) {
            return;
        }

        (new Module())
            ->setCode(PayPal::getModuleCode())
            ->setFullNamespace(PayPal::class)
            ->setVersion('6.0.4')
            ->setType(BaseModule::PAYMENT_MODULE_TYPE)
            ->setCategory('payment')
            ->setActivate(BaseModule::IS_ACTIVATED)
            ->save($this->getPropelConnection());
    }
}
