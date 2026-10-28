<?php

declare(strict_types=1);

namespace PayPal\Tests;

// The tests are found by their path, not by an autoload: the shared fixtures are loaded the same way.
require_once __DIR__.'/PaymentPartsFixtures.php';

use PayPal\Controller\PayPalApiController;
use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use PayPal\Service\ChargedAmount;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\Currency;
use Thelia\Module\BalanceChargingPaymentModuleInterface;
use Thelia\Test\IntegrationTestCase;

/**
 * An order of 100 paid with a part of 30 (a gift card) is charged 70 by PayPal, and an order with no part 100: what
 * PayPal is asked for is the answer of the core (ORDER_PAY_GET_TOTAL, the balance of the primary part), never the
 * total of the order. Nothing here calls PayPal.
 *
 * Run from the root of the Thelia project the module is installed in, against a disposable database (see bootstrap.php).
 */
final class BalanceChargingTest extends IntegrationTestCase
{
    use PaymentPartsFixtures;

    public function testTheModuleDeclaresThatItChargesTheBalance(): void
    {
        self::assertTrue(is_subclass_of(PayPal::class, BalanceChargingPaymentModuleInterface::class));
    }

    public function testAnOrderOf100WithAPartOf30IsChargedTheBalance(): void
    {
        $order = $this->orderOf100();
        $this->payInParts($order, '30.000000', '70.000000');

        self::assertSame(100.0, (float) $order->getTotalAmount());
        self::assertSame('70.000000', $this->chargedAmount()->of($order));
        self::assertSame(70.0, $this->chargedAmount()->forPayPal($order));
        self::assertTrue($this->chargedAmount()->isPaidInParts($order));
    }

    public function testAnOrderWithNoPartIsChargedInFull(): void
    {
        $order = $this->orderOf100();

        self::assertSame('100.000000', $this->chargedAmount()->of($order));
        self::assertSame(100.0, $this->chargedAmount()->forPayPal($order));
        self::assertFalse($this->chargedAmount()->isPaidInParts($order));
    }

    public function testThePayPalAmountOfAPartPaidOrderIsTheBalanceWithNoDetailToDisagreeWithIt(): void
    {
        $order = $this->orderOf100();
        $this->payInParts($order, '30.000000', '70.000000');

        $amount = $this->service()->generateAmount($order, (new Currency())->setCode('EUR'));

        self::assertSame(70.0, (float) $amount->getTotal());
        self::assertSame('EUR', $amount->getCurrency());
        self::assertNull($amount->getDetails());
    }

    public function testThePayPalAmountOfAnOrderWithNoPartIsItsTotal(): void
    {
        $amount = $this->service()->generateAmount($this->orderOf100(), (new Currency())->setCode('EUR'));

        self::assertSame(100.0, (float) $amount->getTotal());
        self::assertNull($amount->getDetails());
    }

    public function testThePayPalOrderRecordsWhatPayPalIsAskedFor(): void
    {
        $order = $this->orderOf100();
        $this->payInParts($order, '30.000000', '70.000000');
        $recorded = null;
        $this->dispatcher()->addListener(PayPalEvents::PAYPAL_ORDER_CREATE, static function (PayPalOrderEvent $event) use (&$recorded): void {
            $recorded = $event->getPayPalOrder()->getAmount();
            $event->stopPropagation();
        }, 1024);

        $this->service()->generatePayPalOrder($order);

        self::assertSame(70.0, (float) $recorded);
    }

    public function testTheOrderSentToPayPalCarriesTheBalanceAndNothingElse(): void
    {
        $order = $this->orderOf100();
        $this->payInParts($order, '30.000000', '70.000000');
        $controller = (new \ReflectionClass(PayPalApiController::class))->newInstanceWithoutConstructor();

        $body = $controller->orderRequestBody('ORD-1', 'EUR', $this->chargedAmount()->forPayPal($order));

        self::assertSame(['currency_code' => 'EUR', 'value' => 70.0], $body['purchase_units'][0]['amount']);
        self::assertSame(['reference_id', 'amount'], array_keys($body['purchase_units'][0]));
    }

    private function dispatcher(): EventDispatcherInterface
    {
        return static::getContainer()->get('event_dispatcher');
    }

    private function chargedAmount(): ChargedAmount
    {
        return new ChargedAmount($this->dispatcher());
    }

    private function service(): PayPalBaseService
    {
        $service = (new \ReflectionClass(PayPalBaseService::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(PayPalBaseService::class, 'dispatcher'))->setValue($service, $this->dispatcher());

        return $service;
    }
}
