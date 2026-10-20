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
     * @throws \RuntimeException       when the answer does not say the outcome
     */
    public static function captureResult(array $capture): PaymentOperationResult
    {
        $id = trim((string) ($capture['id'] ?? ''));
        $status = (string) ($capture['status'] ?? '');

        return match ($status) {
            'COMPLETED' => PaymentOperationResult::succeeded($id),
            'PENDING' => PaymentOperationResult::pending($id),
            'DECLINED', 'FAILED' => throw PaymentRefusedException::withProviderCode(\sprintf('PayPal did not capture the payment: %s', $status), $status),
            // Anything else may have taken the money: Thelia keeps the line pending until
            // PayPal says, instead of offering a second capture.
            default => throw new \RuntimeException(\sprintf('PayPal answered the capture without saying its outcome (%s).', '' !== $status ? $status : 'no status')),
        };
    }

    /**
     * The error PayPal answered, worded for the merchant.
     *
     * @param array<string, mixed> $error a PayPal error body
     */
    public static function errorOf(array $error): string
    {
        $issue = self::issueOf($error);
        $description = (string) ($error['details'][0]['description'] ?? $error['message'] ?? '');

        return '' === $description
            ? \sprintf('PayPal refused: %s', $issue)
            : \sprintf('PayPal refused: %s (%s)', $issue, $description);
    }

    /**
     * The refusal Thelia writes on the journal line: the error worded for the merchant, and the
     * issue PayPal named as the code of the provider.
     *
     * @param array<string, mixed> $error a PayPal error body
     */
    public static function refusalOf(array $error): PaymentRefusedException
    {
        return PaymentRefusedException::withProviderCode(self::errorOf($error), self::issueOf($error));
    }

    /**
     * @param array<string, mixed> $error a PayPal error body
     */
    private static function issueOf(array $error): string
    {
        return (string) ($error['details'][0]['issue'] ?? $error['name'] ?? 'UNKNOWN_ERROR');
    }
}
