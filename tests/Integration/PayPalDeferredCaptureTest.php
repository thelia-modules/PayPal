<?php

declare(strict_types=1);

namespace PayPal\Tests\Integration;

use PayPal\PayPal;
use PayPal\Service\PayPalApiService;
use PayPal\Service\PayPalDeferredCapture;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Thelia\Domain\Payment\Enum\PaymentTransactionState;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;
use Thelia\Domain\Payment\Service\PaymentTransactionRecorder;
use Thelia\Domain\Payment\Service\PaymentTransactionTotalsReader;
use Thelia\Model\Module;
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

        $this->expectException(PaymentRefusedException::class);
        $this->expectExceptionMessage('AUTHORIZATION_EXPIRED');

        $this->service([$this->json(422, ['name' => 'UNPROCESSABLE_ENTITY', 'details' => [['issue' => 'AUTHORIZATION_EXPIRED', 'description' => 'Authorization has expired.']]])])->capture($order, 40.0, $line);
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
