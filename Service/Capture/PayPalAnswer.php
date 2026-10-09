<?php

declare(strict_types=1);

namespace PayPal\Service\Capture;

use Thelia\Domain\Payment\DTO\PaymentOperationResult;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;

/**
 * What PayPal's answers (Orders API and Payments API v2) say, in the words of Thelia's payment journal.
 */
final class PayPalAnswer
{
    /** Statuses of an authorization that still holds the amount. */
    private const HOLDING = ['CREATED', 'PARTIALLY_CAPTURED', 'PENDING'];

    /**
     * The authorization PayPal granted on an order it authorized, when it holds an amount.
     *
     * @param array<string, mixed> $order the answer to POST /v2/checkout/orders/{id}/authorize
     */
    public static function authorizationOf(array $order): ?PayPalAuthorization
    {
        $authorization = $order['purchase_units'][0]['payments']['authorizations'][0] ?? null;

        if ('COMPLETED' !== ($order['status'] ?? null) || !\is_array($authorization)) {
            return null;
        }

        $id = trim((string) ($authorization['id'] ?? ''));
        $amount = trim((string) ($authorization['amount']['value'] ?? ''));

        if ('' === $id || '' === $amount || !\in_array($authorization['status'] ?? null, self::HOLDING, true)) {
            return null;
        }

        return new PayPalAuthorization($id, $amount);
    }

    /**
     * @param array<string, mixed> $capture the answer to POST /v2/payments/authorizations/{id}/capture
     *
     * @throws PaymentRefusedException when PayPal declined the capture
     */
    public static function captureResult(array $capture): PaymentOperationResult
    {
        $id = trim((string) ($capture['id'] ?? ''));
        $status = (string) ($capture['status'] ?? '');

        return match ($status) {
            'COMPLETED' => PaymentOperationResult::succeeded($id),
            'PENDING' => PaymentOperationResult::pending($id),
            default => throw new PaymentRefusedException(\sprintf('PayPal did not capture the payment: %s', '' !== $status ? $status : 'no status')),
        };
    }

    /**
     * The error PayPal answered, worded for the merchant.
     *
     * @param array<string, mixed> $error a PayPal error body
     */
    public static function errorOf(array $error): string
    {
        $detail = $error['details'][0] ?? [];
        $issue = (string) ($detail['issue'] ?? $error['name'] ?? 'UNKNOWN_ERROR');
        $description = (string) ($detail['description'] ?? $error['message'] ?? '');

        return '' === $description
            ? \sprintf('PayPal refused: %s', $issue)
            : \sprintf('PayPal refused: %s (%s)', $issue, $description);
    }
}
