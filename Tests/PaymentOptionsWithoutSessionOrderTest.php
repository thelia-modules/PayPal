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

// The tests are found by their path, not by an autoload: the shared traits are loaded the same way.
require_once __DIR__.'/ActivatedPayPal.php';

use PayPal\EventListeners\PaymentModuleOptionListener;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Api\Bridge\Propel\Event\PaymentModuleOptionEvent;
use Thelia\Api\Resource\PaymentModuleOption;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Cart;
use Thelia\Model\LangQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Test\IntegrationTestCase;

/**
 * The Thelia 3 checkout keeps no order in the session (`thelia.order`): the postage it computed is on the cart. The
 * payment step still offers the direct payment with PayPal, and the planified payments whose bounds the total of the
 * cart, postage included, fits.
 *
 * Run from the root of the Thelia project the module is installed in, against a disposable database (see bootstrap.php).
 */
final class PaymentOptionsWithoutSessionOrderTest extends IntegrationTestCase
{
    use ActivatedPayPal;

    private ?string $planifiedPayments = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planifiedPayments = PayPal::getConfigValue('method_planified_payment');
        PayPal::setConfigValue('method_planified_payment', 1);
    }

    protected function tearDown(): void
    {
        PayPal::setConfigValue('method_planified_payment', $this->planifiedPayments);

        parent::tearDown();
    }

    public function testTheDirectPaymentAndThePlanifiedPaymentsTheCartFitsAreOfferedWithoutAnOrderInTheSession(): void
    {
        $fits = $this->planifiedPayment('Trois fois', '5', '0');
        $tooSmall = $this->planifiedPayment('Dix fois', '50', '0');
        // An empty cart: its total is the postage the checkout set on it.
        $cart = $this->createFixtureFactory()->cart();
        $cart->setPostage('10')->save($this->getPropelConnection());

        $codes = $this->offeredOptionCodes($cart);

        self::assertContains('paypal', $codes, 'the direct payment, without an order in the session');
        self::assertContains('PaypalPlanifiedPayment_'.$fits->getId(), $codes, 'a planified payment from 5, the postage of the cart counted');
        self::assertNotContains('PaypalPlanifiedPayment_'.$tooSmall->getId(), $codes, 'a planified payment from 50');
    }

    /**
     * @return list<string> the codes of the options the module offers for the cart of the session, which holds no order
     */
    private function offeredOptionCodes(Cart $cart): array
    {
        $session = new Session(new MockArraySessionStorage());
        $session->setLang(LangQuery::create()->findOneByByDefault(1) ?? self::fail('No default language in the test shop.'));
        $session->setSessionCart($cart);
        self::assertNull($session->get('thelia.order'));
        $request = new Request();
        $request->setSession($session);
        $requests = new RequestStack();
        $requests->push($request);

        $module = ModuleQuery::create()->findOneByCode('PayPal') ?? self::fail('PayPal is not registered in the test shop.');
        $event = new PaymentModuleOptionEvent($module, $cart);
        $dispatcher = static::getContainer()->get(EventDispatcherInterface::class);
        self::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);
        (new PaymentModuleOptionListener($requests, $dispatcher))->getPaymentModuleOptions($event);

        $codes = [];
        foreach ($event->getPaymentModuleOptionGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                self::assertInstanceOf(PaymentModuleOption::class, $option);
                $codes[] = (string) $option->getCode();
            }
        }

        return $codes;
    }

    private function planifiedPayment(string $title, string $minAmount, string $maxAmount): PaypalPlanifiedPayment
    {
        $locale = (string) LangQuery::create()->findOneByByDefault(1)?->getLocale();
        $planifiedPayment = (new PaypalPlanifiedPayment())
            ->setPaypalId('P-TEST-'.bin2hex(random_bytes(4)))
            ->setFrequency('MONTH')
            ->setFrequencyInterval(1)
            ->setCycle(3)
            ->setMinAmount($minAmount)
            ->setMaxAmount($maxAmount)
            ->setPosition(1)
            ->setLocale($locale)
            ->setTitle($title);
        $planifiedPayment->save($this->getPropelConnection());

        return $planifiedPayment;
    }
}
