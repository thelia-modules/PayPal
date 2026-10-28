<?php

declare(strict_types=1);

namespace PayPal\Tests;

use Thelia\Model\ModuleQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderPaymentPart;
use Thelia\Test\FixtureFactory;

/**
 * An order whose payment is split the way the core writes it when a gift card pays a part of it: an instant part,
 * already settled, and the part of PayPal, the balance, still to be charged.
 */
trait PaymentPartsFixtures
{
    abstract protected function createFixtureFactory(): FixtureFactory;

    abstract protected function getPropelConnection(): \Propel\Runtime\Connection\ConnectionInterface;

    /**
     * An order of 100 (its postage, no line) to be paid with PayPal.
     */
    private function orderOf100(?\Thelia\Model\Customer $customer = null): Order
    {
        $fixtures = $this->createFixtureFactory();

        return $fixtures->order($customer ?? $fixtures->customer($fixtures->customerTitle()), [
            'paymentModuleCode' => 'PayPal',
            'postage' => 100,
        ]);
    }

    private function payInParts(Order $order, string $instantAmount, string $balance): void
    {
        $this->writePart($order, 'Cheque', 'Gift card', $instantAmount, 'settled', false, 0);
        $this->writePart($order, 'PayPal', 'PayPal', $balance, 'pending', true, 1);
    }

    private function writePart(Order $order, string $moduleCode, string $title, string $amount, string $state, bool $primary, int $position): void
    {
        $module = ModuleQuery::create()->findOneByCode($moduleCode) ?? throw new \RuntimeException("No $moduleCode module installed.");

        (new OrderPaymentPart())
            ->setOrderId($order->getId())
            ->setPaymentModuleId($module->getId())
            ->setPaymentModuleTitle($title)
            ->setAmount($amount)
            ->setState($state)
            ->setIsPrimary($primary)
            ->setPosition($position)
            ->save($this->getPropelConnection());
    }
}
