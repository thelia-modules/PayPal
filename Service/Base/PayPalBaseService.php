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

namespace PayPal\Service\Base;

use Monolog\Logger;
use PayPal\Api\Amount;
use PayPal\Api\FuturePayment;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\Model\PaypalCart;
use PayPal\Model\PaypalCartQuery;
use PayPal\Model\PaypalOrder;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\PayPal;
use PayPal\Rest\ApiContext;
use PayPal\Service\PayPalLoggerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Thelia\Core\Event\Cart\CartRestoreEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Currency;
use Thelia\Model\Order;
use Thelia\Model\OrderAddressQuery;

class PayPalBaseService
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var RequestStack */
    protected $requestStack;

    /** @var RouterInterface */
    protected $router;

    /** @var OAuthTokenCredential */
    protected $authTokenCredential;

    /**
     * PayPalBaseService constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(EventDispatcherInterface $dispatcher, RequestStack $requestStack, RouterInterface $router)
    {
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->router = $router;

        $this->authTokenCredential = new OAuthTokenCredential(self::getLogin(), self::getPassword());
    }

    /**
     * @param Order $order
     * @param string|null $creditCardId
     * @param PaypalPlanifiedPayment $planifiedPayment
     * @return PayPalOrderEvent
     */
    public function generatePayPalOrder(Order $order, $creditCardId = null, PaypalPlanifiedPayment $planifiedPayment = null)
    {
        $payPalOrder = new PaypalOrder();
        $payPalOrder
            ->setId($order->getId())
            ->setAmount($order->getTotalAmount())
        ;

        if (null !== $creditCardId) {
            $payPalOrder->setCreditCardId($creditCardId);
        }

        if (null !== $planifiedPayment) {
            /** @var \Thelia\Model\Lang $lang */
            $lang = $this->requestStack->getCurrentRequest()->getSession()->get('thelia.current.lang');
            $planifiedPayment->getTranslation($lang->getLocale());

            $payPalOrder
                ->setPlanifiedTitle($planifiedPayment->getTitle())
                ->setPlanifiedDescription($planifiedPayment->getDescription())
                ->setPlanifiedFrequency($planifiedPayment->getFrequency())
                ->setPlanifiedFrequencyInterval($planifiedPayment->getFrequencyInterval())
                ->setPlanifiedCycle($planifiedPayment->getCycle())
                ->setPlanifiedMinAmount($planifiedPayment->getMinAmount())
                ->setPlanifiedMaxAmount($planifiedPayment->getMaxAmount())
            ;
        }

        $payPalOrderEvent = new PayPalOrderEvent($payPalOrder);
        $this->dispatcher->dispatch(PayPalEvents::PAYPAL_ORDER_CREATE, $payPalOrderEvent);

        return $payPalOrderEvent;
    }

    /**
     * @param PaypalOrder $payPalOrder
     * @param $state
     * @param string|null $paymentId
     * @param string|null $agreementId
     * @return PayPalOrderEvent
     */
    public function updatePayPalOrder(PaypalOrder $payPalOrder, $state, $paymentId = null, $agreementId = null)
    {
        $payPalOrder->setState($state);

        if (null !== $paymentId) {
            $payPalOrder->setPaymentId($paymentId);
        }

        if (null !== $agreementId) {
            $payPalOrder->setAgreementId($agreementId);
        }

        $payPalOrderEvent = new PayPalOrderEvent($payPalOrder);
        $this->dispatcher->dispatch(PayPalEvents::PAYPAL_ORDER_UPDATE, $payPalOrderEvent);

        return $payPalOrderEvent;
    }

    /**
     * @return PaypalCart
     */
    public function getCurrentPayPalCart()
    {
        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $cart = $session->getSessionCart($this->dispatcher);

        if (null === $cart) {
            $cartEvent = new CartRestoreEvent();
            $this->dispatcher->dispatch(TheliaEvents::CART_RESTORE_CURRENT, $cartEvent);

            $cart = $cartEvent->getCart();
        }

        if (null === $payPalCart = PaypalCartQuery::create()->findOneById($cart->getId())) {
            $payPalCart = new PaypalCart();
            $payPalCart->setId($cart->getId());
        }

        return $payPalCart;
    }

    /**
     * @param string $method
     * @param array $fundingInstruments
     * @param PayerInfo $payerInfo
     * @return Payer
     */
    public static function generatePayer($method = PayPal::PAYPAL_METHOD_PAYPAL, $fundingInstruments = [], PayerInfo $payerInfo = null)
    {
        $payer = new Payer();
        $payer->setPaymentMethod($method);

        // Never set empty instruments when communicating with PayPal
        if (count($fundingInstruments) > 0) {
            $payer->setFundingInstruments($fundingInstruments);
        }

        if (null !== $payerInfo) {
            $payer->setPayerInfo($payerInfo);
        }

        return $payer;
    }

    public static function generatePayerInfo($data = [])
    {
        $payerInfo = new PayerInfo($data);

        return $payerInfo;
    }

    public static function generateShippingAddress(Order $order)
    {
        if (null !== $orderAddress = OrderAddressQuery::create()->findOneById($order->getDeliveryOrderAddressId())) {
            $shippingAddress = new ShippingAddress();

            if (null !== $state = $orderAddress->getState()) {
                $payPalState = $state->getIsocode();
            } else {
                $payPalState = 'CA';
            }

            $shippingAddress
                ->setLine1($orderAddress->getAddress1())
                ->setCity($orderAddress->getCity())
                ->setPostalCode($orderAddress->getZipcode())
                ->setCountryCode($orderAddress->getCountry()->getIsoalpha2())
                ->setState($payPalState)
            ;

            if (null !== $orderAddress->getAddress2()) {

                if (null !== $orderAddress->getAddress3()) {
                    $shippingAddress->setLine2($orderAddress->getAddress2() . ' ' . $orderAddress->getAddress3());
                } else {
                    $shippingAddress->setLine2($orderAddress->getAddress2());
                }
            } elseif (null !== $orderAddress->getAddress3()) {
                $shippingAddress->setLine2($orderAddress->getAddress3());
            }

            if (null !== $orderAddress->getStateId()) {
                //$shippingAddress->setState($orderAddress->getState()->getIsocode());
            }

            return $shippingAddress;
        } else {
            $message = Translator::getInstance()->trans(
                'Order address no found to generate PayPal shipping address',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }
    }

    /**
     * @param Order $order
     * @param Currency $currency
     * @return Amount
     */
    public function generateAmount(Order $order, Currency $currency)
    {
        // Specify the payment amount.
        $amount = new Amount();
        $amount->setCurrency($currency->getCode());
        $amount->setTotal($order->getTotalAmount());

        return $amount;
    }

    /**
     * @param Cart $cart
     * @param Currency $currency
     * @return Amount
     */
    public function generateAmountFromCart(Cart $cart, Currency $currency)
    {
        // Specify the payment amount.
        $amount = new Amount();
        $amount->setCurrency($currency->getCode());
        $amount->setTotal($cart->getTaxedAmount(Country::getDefaultCountry()));

        return $amount;
    }

    /**
     * @param Amount $amount
     * @param string $description
     * @return Transaction
     */
    public function generateTransaction(Amount $amount, $description = '')
    {
        // ###Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it. Transaction is created with
        // a `Payee` and `Amount` types
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription($description);

        return $transaction;
    }

    public function getAccessToken()
    {
        $config = self::getApiContext()->getConfig();
        $accessToken = $this->authTokenCredential->getAccessToken($config);

        return $accessToken;
    }

    public function getRefreshToken()
    {
        $refreshToken = FuturePayment::getRefreshToken($this->getAccessToken(), self::getApiContext());

        return $refreshToken;
    }

    /**
     * SDK Configuration
     *
     *@return ApiContext
     */
    public static function getApiContext()
    {
        $apiContext = new ApiContext();

        // Alternatively pass in the configuration via a hashmap.
        // The hashmap can contain any key that is allowed in
        // sdk_config.ini
        $apiContext->setConfig([
            'acct1.ClientId' => self::getLogin(),
            'acct1.ClientSecret' => self::getPassword(),
            'http.ConnectionTimeOut' => 30,
            'http.Retry' => 1,
            'mode' => self::getMode(),
            'log.LogEnabled' => true,
            'log.FileName' => '../log/PayPal.log',
            'log.LogLevel' => 'INFO',
            'cache.enabled' => true,
            'cache.FileName' => '../cache/prod/PayPal.cache',
            'http.headers.PayPal-Partner-Attribution-Id' => 'Thelia_Cart',
        ]);

        return $apiContext;
    }

    /**
     * @return string
     */
    public static function getLogin()
    {
        if (PayPal::getConfigValue('sandbox') == 1) {
            $login = PayPal::getConfigValue('sandbox_login');
        } else {
            $login = PayPal::getConfigValue('login');
        }

        return $login;
    }

    /**
     * @return string
     */
    public static function getPassword()
    {
        if (PayPal::getConfigValue('sandbox') == 1) {
            $password = PayPal::getConfigValue('sandbox_password');
        } else {
            $password = PayPal::getConfigValue('password');
        }

        return $password;
    }

    /**
     * @return string
     */
    public static function getMerchantId()
    {
        if (PayPal::getConfigValue('sandbox') == 1) {
            $login = PayPal::getConfigValue('sandbox_merchant_id');
        } else {
            $login = PayPal::getConfigValue('merchant_id');
        }

        return $login;
    }

    /**
     * @return string
     */
    public static function getMode()
    {
        if (PayPal::getConfigValue('sandbox') == 1) {
            $mode = 'sandbox';
        } else {
            $mode = 'live';
        }

        return $mode;
    }

    public static function isSandboxMode()
    {
        if (self::getMode() === 'live') {
            return false;
        } else {
            return true;
        }
    }
}
