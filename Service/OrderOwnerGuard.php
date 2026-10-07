<?php

declare(strict_types=1);

namespace PayPal\Service;

use Thelia\Model\Customer;
use Thelia\Model\Order;

/**
 * Whether the order a request names is the one of the signed-in customer: the payment routes take an order id from
 * the url or the body, and only the owner of that order may act on it.
 */
final readonly class OrderOwnerGuard
{
    public function isOwnedBy(?Order $order, ?Customer $customer): bool
    {
        return null !== $order
            && null !== $customer
            && null !== $customer->getId()
            && (int) $order->getCustomerId() === (int) $customer->getId();
    }
}
