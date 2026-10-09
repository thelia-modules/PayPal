<?php

declare(strict_types=1);

namespace PayPal\Service\Capture;

/**
 * An authorization PayPal granted on an order: what the shop captures, or releases, later.
 */
final readonly class PayPalAuthorization
{
    /**
     * @param string $amount in the major unit, as PayPal writes it
     */
    public function __construct(
        public string $id,
        public string $amount,
    ) {
    }
}
