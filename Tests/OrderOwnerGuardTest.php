<?php

namespace PayPal\Tests;

use PayPal\Service\OrderOwnerGuard;
use Thelia\Model\Customer;
use Thelia\Model\Order;
use Thelia\Test\IntegrationTestCase;

/**
 * The payment routes act on the order a request names: only its owner may.
 */
final class OrderOwnerGuardTest extends IntegrationTestCase
{
    public function testTheOwnerOfTheOrderMayActOnIt(): void
    {
        self::assertTrue((new OrderOwnerGuard())->isOwnedBy($this->order(12), $this->customer(12)));
    }

    public function testAnotherCustomerMayNot(): void
    {
        self::assertFalse((new OrderOwnerGuard())->isOwnedBy($this->order(12), $this->customer(13)));
    }

    public function testNobodySignedInMayNot(): void
    {
        self::assertFalse((new OrderOwnerGuard())->isOwnedBy($this->order(12), null));
    }

    public function testAnUnknownOrderIsNotOwnedByAnyone(): void
    {
        self::assertFalse((new OrderOwnerGuard())->isOwnedBy(null, $this->customer(12)));
    }

    private function order(int $customerId): Order
    {
        return (new Order())->setCustomerId($customerId);
    }

    private function customer(int $id): Customer
    {
        return (new Customer())->setId($id);
    }
}
