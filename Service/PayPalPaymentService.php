<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace PayPal\Service;

use Monolog\Logger;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\FuturePayment;
use PayPal\Api\OpenIdTokeninfo;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Cart;
use Thelia\Model\Currency;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Order;
use Thelia\Tools\URL;


/**
 * Class PayPalPaymentService
 * @package PayPal\Service
 */
class PayPalPaymentService extends PayPalBaseService
{
    /**
     * Create a payment using a previously obtained
     * credit card id. The corresponding credit
     * card is used as the funding instrument.
     *
     * @param Order $order
     * @param bool $future
     * @param string|null $creditCardId
     * @param string|null $description
     * @return Payment
     */
    public function makePayment(Order $order, $creditCardId = null, $description = null, $future = false)
    {
        $payPalOrderEvent = $this->generatePayPalOrder($order);

        if (null !== $creditCardId) {
            $creditCardToken = new CreditCardToken();
            $creditCardToken->setCreditCardId($creditCardId);

            $fundingInstrument = new FundingInstrument();
            $fundingInstrument->setCreditCardToken($creditCardToken);

            $payer = self::generatePayer(PayPal::PAYPAL_METHOD_CREDIT_CARD, [$fundingInstrument]);
        } else {
            $payer = self::generatePayer();
        }

        // Specify the payment amount.
        if (null === $currency = CurrencyQuery::create()->findOneById($order->getCurrencyId())) {
            $currency = Currency::getDefaultCurrency();
        }

        $amount = $this->generateAmount($order, $currency);

        $transaction = $this->generateTransaction($amount, $description);

        $payment = $this->generatePayment($order, $payer, $transaction, $future);

        $this->updatePayPalOrder($payPalOrderEvent->getPayPalOrder(), $payment->getState(), $payment->getId());

        return $payment;
    }

    public function makePaymentFromCart(Cart $cart, $description = null, $future = false, $fromCartView = true)
    {
        $payer = self::generatePayer();

        // Specify the payment amount.
        if (null === $currency = CurrencyQuery::create()->findOneById($cart->getCurrencyId())) {
            $currency = Currency::getDefaultCurrency();
        }

        $amount = $this->generateAmountFromCart($cart, $currency);

        $transaction = $this->generateTransaction($amount, $description);

        $payment = $this->generatePaymentFromCart($cart, $payer, $transaction, $future, $fromCartView);

        //$this->updatePayPalOrder($payPalOrderEvent->getPayPalOrder(), $payment->getState(), $payment->getId());

        return $payment;
    }

    /**
     * Completes the payment once buyer approval has been
     * obtained. Used only when the payment method is 'paypal'
     *
     * @param string $paymentId id of a previously created
     * 		payment that has its payment method set to 'paypal'
     * 		and has been approved by the buyer.
     *
     * @param string $payerId PayerId as returned by PayPal post
     * 		buyer approval.
     *
     * @return Payment
     */
    public function executePayment($paymentId, $payerId, Details $details = null)
    {
        $payment = $this->getPaymentDetails($paymentId);
        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);

        if (null !== $details) {
            $amount = new Amount();
            $totalDetails = (float)$details->getShipping() + (float)$details->getTax() + (float)$details->getSubtotal();
            $amount
                ->setCurrency('EUR')
                ->setTotal($totalDetails)
                ->setDetails($details)
            ;

            $transaction = new Transaction();
            $transaction->setAmount($amount);

            $paymentExecution->addTransaction($transaction);
        }

        $payment = $payment->execute($paymentExecution, self::getApiContext());

        return $payment;
    }

    public function createDetails($shipping = 0, $shippingTax = 0, $subTotal = 0)
    {
        $details = new Details();
        $details
            ->setShipping($shipping)
            ->setTax($shippingTax)
            ->setSubtotal($subTotal)
        ;

        return $details;
    }

    /**
     * Retrieves the payment information based on PaymentID from Paypal APIs
     *
     * @param $paymentId
     *
     * @return Payment
     */
    public function getPaymentDetails($paymentId)
    {
        $payment = Payment::get($paymentId, self::getApiContext());

        return $payment;
    }

    /**
     * @param $authorizationCode
     * @return OpenIdTokeninfo
     * @throws PayPalConnectionException
     */
    public function generateAccessToken($authorizationCode)
    {
        try {
            // Obtain Authorization Code from Code, Client ID and Client Secret
            $accessToken = OpenIdTokeninfo::createFromAuthorizationCode(
                ['code' => $authorizationCode],
                null,
                null,
                self::getApiContext()
            );

            return $accessToken;
        } catch (PayPalConnectionException $ex) {
            PayPalLoggerService::log($ex->getMessage(), [], Logger::ERROR);
            throw $ex;
        }
    }

    /**
     * @param $type
     * @param $number
     * @param $expireMonth
     * @param $expireYear
     * @param $cvv2
     * @return string
     * @throws \Exception
     */
    public function getPayPalCreditCardId($type, $number, $expireMonth, $expireYear, $cvv2)
    {
        try {
            $card = new CreditCard();
            $card->setType($type);
            $card->setNumber((int)$number);
            $card->setExpireMonth((int)$expireMonth);
            $card->setExpireYear((int)$expireYear);
            $card->setCvv2($cvv2);

            $card->create(self::getApiContext());

            return $card->getId();
        } catch (\Exception $e) {
            PayPalLoggerService::log($e->getMessage(), [], Logger::ERROR);
            throw new \Exception(Translator::getInstance()->trans('Credit card is invalid', [], PayPal::DOMAIN_NAME));
        }
    }

    /**
     * @param Order $order
     * @param Payer $payer
     * @param Transaction $transaction
     * @param bool $future
     * @return FuturePayment|Payment
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function generatePayment(Order $order, Payer $payer, Transaction $transaction, $future = false)
    {
        if ($future) {
            $payment = new FuturePayment();
            $payment->setIntent('authorize');
        } else {
            $payment = new Payment();
            $payment->setIntent('sale');
        }

        $payment
            ->setRedirectUrls($this->getRedirectUrls($order))
            ->setPayer($payer)
            ->setTransactions([$transaction])
        ;

        $clientMetadataId = '123123456';

        try {

            if ($future) {

                //$authorizationCode = self::getAuthorizationCode();
                $refreshToken = $this->getRefreshToken();
                //$refreshToken = FuturePayment::getRefreshToken($this->getAuthorizationCode(), self::getApiContext());
                $payment->updateAccessToken($refreshToken, self::getApiContext());
                $payment->create(self::getApiContext(), $clientMetadataId);

            } else {
                $payment->create(self::getApiContext());
            }

            return $payment;

        }  catch (PayPalConnectionException $e) {
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::CRITICAL
            );
            throw $e;
        } catch (\Exception $e) {
            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::CRITICAL
            );
            throw $e;
        }
    }

    /**
     * @param Cart $cart
     * @param Payer $payer
     * @param Transaction $transaction
     * @param bool $future
     * @return FuturePayment|Payment
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function generatePaymentFromCart(Cart $cart, Payer $payer, Transaction $transaction, $future = false, $fromCartView = true)
    {
        if ($future) {
            $payment = new FuturePayment();
            $payment->setIntent('authorize');
        } else {
            $payment = new Payment();
            $payment->setIntent('sale');
        }

        if ($fromCartView) {
            $payment->setRedirectUrls($this->getRedirectCartUrls($cart));
        } else {
            $payment->setRedirectUrls($this->getRedirectInvoiceUrls($cart));
        }
        $payment
            ->setPayer($payer)
            ->setTransactions([$transaction])
        ;

        $clientMetadataId = '123123456';

        try {

            if ($future) {

                //$authorizationCode = self::getAuthorizationCode();
                $refreshToken = $this->getRefreshToken();
                //$refreshToken = FuturePayment::getRefreshToken($this->getAuthorizationCode(), self::getApiContext());
                $payment->updateAccessToken($refreshToken, self::getApiContext());
                $payment->create(self::getApiContext(), $clientMetadataId);

            } else {
                $payment->create(self::getApiContext());
            }

            return $payment;

        }  catch (PayPalConnectionException $e) {
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log(
                $message,
                [],
                Logger::CRITICAL
            );
            throw $e;
        } catch (\Exception $e) {
            PayPalLoggerService::log(
                $e->getMessage(),
                [],
                Logger::CRITICAL
            );
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return RedirectUrls
     */
    public function getRedirectUrls(Order $order)
    {
        $redirectUrls = new RedirectUrls();
        $urlOk = URL::getInstance()->absoluteUrl('/module/paypal/ok/' . $order->getId());
        $urlCancel = URL::getInstance()->absoluteUrl('/module/paypal/cancel/' . $order->getId());
        $redirectUrls->setReturnUrl($urlOk);
        $redirectUrls->setCancelUrl($urlCancel);

        return $redirectUrls;
    }

    /**
     * @param Cart $cart
     * @return RedirectUrls
     */
    public function getRedirectCartUrls(Cart $cart)
    {
        $redirectUrls = new RedirectUrls();
        $urlOk = URL::getInstance()->absoluteUrl('/module/paypal/express/checkout/ok/' . $cart->getId());
        $urlCancel = URL::getInstance()->absoluteUrl('/module/paypal/express/checkout/ko/' . $cart->getId());
        $redirectUrls->setReturnUrl($urlOk);
        $redirectUrls->setCancelUrl($urlCancel);

        return $redirectUrls;
    }

    /**
     * @param Cart $cart
     * @return RedirectUrls
     */
    public function getRedirectInvoiceUrls(Cart $cart)
    {
        $redirectUrls = new RedirectUrls();
        $urlOk = URL::getInstance()->absoluteUrl('/module/paypal/invoice/express/checkout/ok/' . $cart->getId());
        $urlCancel = URL::getInstance()->absoluteUrl('/module/paypal/invoice/express/checkout/ko/' . $cart->getId());
        $redirectUrls->setReturnUrl($urlOk);
        $redirectUrls->setCancelUrl($urlCancel);

        return $redirectUrls;
    }
}
