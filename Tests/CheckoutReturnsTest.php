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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Customer;
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

    private function request(string $uri, Customer $customer): Response
    {
        $request = Request::create($uri);
        $session = static::getContainer()->get('request_stack')->getCurrentRequest()?->getSession();
        self::assertInstanceOf(Session::class, $session);
        $session->setCustomerUser($customer);
        $request->setSession($session);

        return static::$kernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);
    }
}
