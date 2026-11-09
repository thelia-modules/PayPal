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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The payment option the module offers at the payment step (PaymentModuleOptionListener) reads in the language of
 * the shop, and names the brand PayPal, in French, English and German.
 */
final class PaymentOptionTranslationsTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string, 1: string, 2: string}>
     */
    public static function translations(): iterable
    {
        yield 'German title' => ['de_DE', 'Paypal', 'PayPal'];
        yield 'German description' => ['de_DE', 'Paiement direct avec Paypal', 'Direkte Zahlung mit PayPal'];
        yield 'German group' => ['de_DE', 'Choose a payment option', 'Wählen Sie eine Zahlungsoption'];
        yield 'French title' => ['fr_FR', 'Paypal', 'PayPal'];
        yield 'French description' => ['fr_FR', 'Paiement direct avec Paypal', 'Paiement direct avec PayPal'];
        yield 'English title' => ['en_US', 'Paypal', 'PayPal'];
        yield 'English description' => ['en_US', 'Paiement direct avec Paypal', 'Direct payment with PayPal'];
    }

    #[DataProvider('translations')]
    public function testTheOptionReadsInTheLanguageOfTheShop(string $locale, string $key, string $expected): void
    {
        $file = \dirname(__DIR__).'/I18n/'.$locale.'.php';
        self::assertFileExists($file);

        self::assertSame($expected, (require $file)[$key] ?? null);
    }

    public function testTheListenerStillAsksForTheseKeys(): void
    {
        $listener = (string) file_get_contents(\dirname(__DIR__).'/EventListeners/PaymentModuleOptionListener.php');

        foreach (['Choose a payment option', 'Paypal', 'Paiement direct avec Paypal'] as $key) {
            self::assertStringContainsString("trans('".$key."'", $listener);
        }
    }
}
