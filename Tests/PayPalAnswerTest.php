<?php

declare(strict_types=1);

namespace PayPal\Tests;

use PayPal\Service\Capture\PayPalAnswer;
use PHPUnit\Framework\TestCase;
use Thelia\Domain\Payment\Enum\PaymentTransactionState;
use Thelia\Domain\Payment\Exception\PaymentRefusedException;

/**
 * What PayPal's answers to an authorization and to a capture say, in the words of Thelia's
 * payment journal.
 */
final class PayPalAnswerTest extends TestCase
{
    public function testTheAuthorizationIsReadFromTheOrderPayPalAuthorized(): void
    {
        $authorization = PayPalAnswer::authorizationOf([
            'id' => 'ORDER-1',
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['authorizations' => [['id' => 'AUTH-9', 'status' => 'CREATED', 'amount' => ['currency_code' => 'EUR', 'value' => '120.00']]]]]],
        ]);

        self::assertSame('AUTH-9', $authorization?->id);
        self::assertSame('120.00', $authorization?->amount);
    }

    public function testAnOrderWithoutAnAuthorizationHasNone(): void
    {
        self::assertNull(PayPalAnswer::authorizationOf(['id' => 'ORDER-1', 'status' => 'PAYER_ACTION_REQUIRED']));
        self::assertNull(PayPalAnswer::authorizationOf([
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['authorizations' => [['id' => 'AUTH-9', 'status' => 'DENIED', 'amount' => ['value' => '120.00']]]]]],
        ]), 'A denied authorization holds nothing.');
    }

    public function testACompletedCaptureSucceedsUnderItsOwnReference(): void
    {
        $result = PayPalAnswer::captureResult(['id' => 'CAP-3', 'status' => 'COMPLETED']);

        self::assertSame(PaymentTransactionState::SUCCEEDED, $result->state);
        self::assertSame('CAP-3', $result->pspReference);
    }

    public function testAPendingCaptureWaitsForPayPal(): void
    {
        $result = PayPalAnswer::captureResult(['id' => 'CAP-3', 'status' => 'PENDING', 'status_details' => ['reason' => 'PENDING_REVIEW']]);

        self::assertSame(PaymentTransactionState::PENDING, $result->state);
        self::assertSame('CAP-3', $result->pspReference);
    }

    public function testADeclinedCaptureIsARefusal(): void
    {
        try {
            PayPalAnswer::captureResult(['id' => 'CAP-3', 'status' => 'DECLINED']);
            self::fail('A declined capture is a refusal.');
        } catch (PaymentRefusedException $refusal) {
            self::assertStringContainsString('DECLINED', $refusal->getMessage());
            self::assertSame('DECLINED', $refusal->getProviderCode());
        }
    }

    public function testARefusalCarriesTheIssuePayPalNamed(): void
    {
        $refusal = PayPalAnswer::refusalOf([
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [['issue' => 'AUTHORIZATION_EXPIRED', 'description' => 'Authorization has expired.']],
        ]);

        self::assertSame('PayPal refused: AUTHORIZATION_EXPIRED (Authorization has expired.)', $refusal->getMessage());
        self::assertSame('AUTHORIZATION_EXPIRED', $refusal->getProviderCode());
        self::assertSame('UNPROCESSABLE_ENTITY', PayPalAnswer::refusalOf(['name' => 'UNPROCESSABLE_ENTITY'])->getProviderCode(), 'Without details, the name of the error is the code.');
    }

    public function testTheErrorPayPalAnswersIsWordedForTheMerchant(): void
    {
        $message = PayPalAnswer::errorOf([
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [['issue' => 'AUTHORIZATION_ALREADY_CAPTURED', 'description' => 'Authorization has been previously captured and hence cannot be voided.']],
        ]);

        self::assertSame('PayPal refused: AUTHORIZATION_ALREADY_CAPTURED (Authorization has been previously captured and hence cannot be voided.)', $message);
    }
}
