<?php

declare(strict_types=1);

namespace PayPal\Service;

use PayPal\PayPal;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\Order;
use Thelia\Model\OrderPaymentPartQuery;

/**
 * What PayPal is asked to charge for an order: the balance left once the instant means of the shop (a gift card, for
 * one) have paid their part, the total of the order when it has no part. Every amount sent to PayPal comes from here,
 * never from Order::getTotalAmount(): the core refuses the combination of parts with a module that does not charge
 * this figure (Thelia\Module\BalanceChargingPaymentModuleInterface).
 */
final readonly class ChargedAmount
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Decimal string of six decimals, the answer of AbstractPaymentModule::getAmountToCharge().
     */
    public function of(Order $order): string
    {
        $module = new PayPal();
        $module->setDispatcher($this->dispatcher);

        return $module->getAmountToCharge($order);
    }

    /**
     * The amount as the PayPal REST API takes it: two decimals.
     */
    public function forPayPal(Order $order): float
    {
        return round((float) $this->of($order), 2);
    }

    /**
     * Whether an instant means of the shop pays a part of the order.
     */
    public function isPaidInParts(Order $order): bool
    {
        return OrderPaymentPartQuery::create()->filterByOrderId($order->getId())->exists();
    }
}
