<?php

declare(strict_types=1);

namespace PayPal\Tests\Integration;

use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderStatus;
use Thelia\Module\BaseModule;
use Thelia\Test\FixtureFactory;
use Thelia\Test\WebIntegrationTestCase;
use Thelia\Tests\Support\Flexy\CustomerSessionInjector;

/**
 * The checkout asks PayPal for nothing on an order that waits for no payment: one already paid
 * or authorized, or one the shop cancelled.
 */
final class PayApiTest extends WebIntegrationTestCase
{
    private CustomerSessionInjector $injector;

    private FixtureFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        (new \ReflectionProperty(BaseModule::class, 'moduleIds'))->setValue(null, []);

        if (null === ModuleQuery::create()->findOneByCode(PayPal::getModuleCode())) {
            (new Module())
                ->setCode(PayPal::getModuleCode())
                ->setFullNamespace(PayPal::class)
                ->setVersion('1.0.0')
                ->setType(BaseModule::PAYMENT_MODULE_TYPE)
                ->setCategory('payment')
                ->setActivate(BaseModule::IS_ACTIVATED)
                ->save($this->getPropelConnection());
        }

        $this->injector = new CustomerSessionInjector();
        $this->getService(EventDispatcherInterface::class)->addSubscriber($this->injector);
        $this->factory = new FixtureFactory($this->getPropelConnection());
    }

    protected function tearDown(): void
    {
        if (isset($this->injector)) {
            $this->getService(EventDispatcherInterface::class)->removeSubscriber($this->injector);
            $this->injector->clear();
        }

        parent::tearDown();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function ordersWaitingForNoPayment(): iterable
    {
        yield 'paid' => [OrderStatus::CODE_PAID];
        yield 'cancelled' => [OrderStatus::CODE_CANCELED];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('ordersWaitingForNoPayment')]
    public function testAnOrderWaitingForNoPaymentIsNotPaid(string $statusCode): void
    {
        $order = $this->factory->order(null, ['statusCode' => $statusCode, 'paymentModuleCode' => PayPal::getModuleCode()]);
        $this->injector->setCustomer($order->getCustomer());

        $this->client->request('POST', '/paypal/api/pay', content: json_encode(['order_id' => $order->getId()], \JSON_THROW_ON_ERROR));

        self::assertSame(409, $this->client->getResponse()->getStatusCode());
    }
}
