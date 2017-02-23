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

namespace PayPal\Controller;

use Front\Controller\OrderController;
use Monolog\Logger;
use PayPal\Api\Details;
use PayPal\Api\PayerInfo;
use PayPal\Event\PayPalCartEvent;
use PayPal\Event\PayPalCustomerEvent;
use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Model\PaypalCart;
use PayPal\Model\PaypalCartQuery;
use PayPal\Model\PaypalCustomer;
use PayPal\Model\PaypalCustomerQuery;
use PayPal\Model\PaypalOrder;
use PayPal\Model\PaypalOrderQuery;
use PayPal\PayPal;
use PayPal\Service\PayPalAgreementService;
use PayPal\Service\PayPalCustomerService;
use PayPal\Service\PayPalLoggerService;
use PayPal\Service\PayPalPaymentService;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Model\AddressQuery;
use Thelia\Model\CartQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\CustomerQuery;
use Thelia\Model\CustomerTitleQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatusQuery;
use Thelia\Module\Exception\DeliveryException;
use Thelia\Tools\URL;


/**
 * Class PayPalResponseController
 * @package PayPal\Controller
 */
class PayPalResponseController extends OrderController
{
    /**
     * @param $orderId
     */
    public function cancelAction($orderId)
    {
        if (null !== $order = OrderQuery::create()->findOneById($orderId)) {
            $event = new OrderEvent($order);
            $event->setStatus(OrderStatusQuery::getCancelledStatus()->getId());
            $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);
        }
    }

    /**
     * @param $orderId
     * @return RedirectResponse
     */
    public function okAction($orderId)
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        try {
            $payerId = $this->getRequest()->query->get('PayerID');
            $token = $this->getRequest()->query->get('token');
            $payPalOrder = PaypalOrderQuery::create()->findOneById($orderId);

            if (null !== $payPalOrder && null !== $payerId) {

                $response = $this->executePayment($payPalOrder, $payPalOrder->getPaymentId(), $payerId, $token);
            } else {
                $con->rollBack();
                $message = Translator::getInstance()->trans(
                    'Method okAction => One of this parameter is invalid : $payerId = %payer_id, $orderId = %order_id',
                    [
                        '%payer_id' => $payerId,
                        '%order_id' => $orderId
                    ],
                    PayPal::DOMAIN_NAME
                );

                PayPalLoggerService::log(
                    $message,
                    [
                        'order_id' => $orderId
                    ],
                    Logger::CRITICAL
                );

                $response = $this->getPaymentFailurePageUrl($orderId, $message);
            }
        } catch (PayPalConnectionException $e) {
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log(
                $message,
                [
                    'order_id' => $orderId
                ],
                Logger::CRITICAL
            );
            $response = $this->getPaymentFailurePageUrl($orderId, $e->getMessage());
        } catch (\Exception $e) {
            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'order_id' => $orderId
                ],
                Logger::CRITICAL
            );

            $response = $this->getPaymentFailurePageUrl($orderId, $e->getMessage());
        }

        $con->commit();
        return $response;
    }


    /**
     * @param string $routeId
     * @return RedirectResponse
     */
    public function expressCheckoutAction($routeId = 'cart.view', $fromCartView = true)
    {
        $session = $this->getRequest()->getSession();
        $cart = $session->getSessionCart($this->getDispatcher());

        if (null !== $cart) {
            /** @var PayPalPaymentService $payPalService */
            $payPalService = $this->getContainer()->get(PayPal::PAYPAL_PAYMENT_SERVICE_ID);

            $payment = $payPalService->makePaymentFromCart(
                $cart,
                null,
                false,
                $fromCartView
            );
            $response = new RedirectResponse($payment->getApprovalLink());

            return $response;
        }

        return $this->getUrlFromRouteId('cart.view');
    }

    public function invoiceExpressCheckoutAction()
    {
        return $this->expressCheckoutAction('order.invoice', false);
    }

    /**
     * @param int $cartId
     * @return RedirectResponse
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function invoiceExpressCheckoutOkAction($cartId)
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        try {
            $this->fillCartWithExpressCheckout();

            $response = $this->executeExpressCheckoutAction(false);

        } catch (PayPalConnectionException $e) {
            $con->rollBack();

            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            throw $e;
        } catch(\Exception $e) {
            $con->rollBack();

            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            throw $e;
        }

        $con->commit();
        return $response;
    }

    public function invoiceExpressCheckoutKoAction($cartId)
    {
        return $this->getUrlFromRouteId('order.invoice');
    }

    /**
     * @return RedirectResponse
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function expressCheckoutOkAction()
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        try {
            $this->fillCartWithExpressCheckout();

            $response = $this->getUrlFromRouteId('order.delivery');


        } catch (PayPalConnectionException $e) {
            $con->rollBack();

            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            throw $e;
        } catch(\Exception $e) {
            $con->rollBack();

            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            throw $e;
        }

        $con->commit();
        return $response;
    }

    /**
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function executeExpressCheckoutAction($fromCartView = true)
    {
        if (null === $responseParent = parent::deliver()) {

            if ($fromCartView) {
                return $responseParent;
            }
        }

        $con = Propel::getConnection();
        $con->beginTransaction();

        try {
            $session = $this->getRequest()->getSession();
            $cart = $session->getSessionCart($this->getDispatcher());

            if (null === $payPalCart = PaypalCartQuery::create()->findOneById($cart->getId())) {
                $con->rollBack();
                return $responseParent;
            }

            if (null === $payPalCart->getExpressPaymentId() || null === $payPalCart->getExpressPayerId() || null === $payPalCart->getExpressToken()) {
                $con->rollBack();
                return $responseParent;
            }

            /** @var PayPalPaymentService $payPalPaymentService */
            $payPalPaymentService = $this->container->get(PayPal::PAYPAL_PAYMENT_SERVICE_ID);
            $payment = $payPalPaymentService->getPaymentDetails($payPalCart->getExpressPaymentId());

            $payerInfo = $payment->getPayer()->getPayerInfo();

            //Check if invoice adresse already exist
            if (null === $payerInfo->getBillingAddress()) {
                $line1 = $payerInfo->getShippingAddress()->getLine1();
                $zipCode = $payerInfo->getShippingAddress()->getPostalCode();
            } else {
                $line1 = $payerInfo->getBillingAddress()->getLine1();
                $zipCode = $payerInfo->getBillingAddress()->getPostalCode();
            }

            /** @var \Thelia\Model\Address $invoiceAddress */
            if (null === $invoiceAddress = AddressQuery::create()
                    ->filterByCustomerId($cart->getCustomerId())
                    ->filterByIsDefault(0)
                    ->filterByAddress1($line1)
                    ->filterByZipcode($zipCode)
                    ->findOne()) {

                $event = $this->createAddressEvent($payerInfo);
                $event->setCustomer($cart->getCustomer());

                $this->dispatch(TheliaEvents::ADDRESS_CREATE, $event);
                $invoiceAddress = $event->getAddress();
            }

            if (null === $payPalCustomer = PaypalCustomerQuery::create()->findOneById($cart->getCustomerId())) {
                $payPalCustomer = new PaypalCustomer();
                $payPalCustomer->setId($cart->getCustomerId());
            }

            $payPalCustomer
                ->setPaypalUserId($payerInfo->getPayerId())
                ->setName($payerInfo->getFirstName())
                ->setGivenName($payerInfo->getFirstName() . ' ' . $payerInfo->getLastName())
                ->setFamilyName($payerInfo->getLastName())
                ->setMiddleName($payerInfo->getMiddleName())
                ->setBirthday($payerInfo->getBirthDate())
                ->setLocale($this->getRequest()->getSession()->getLang()->getLocale())
                ->setPhoneNumber($payerInfo->getPhone())
                ->setPayerId($payerInfo->getPayerId())
                ->setPostalCode($payerInfo->getShippingAddress()->getPostalCode())
                ->setCountry($payerInfo->getShippingAddress()->getCountryCode())
                ->setStreetAddress($payerInfo->getShippingAddress()->getLine1() . $payerInfo->getShippingAddress()->getLine2())
            ;

            $payPalCustomerEvent = new PayPalCustomerEvent($payPalCustomer);
            $this->dispatch(PayPalEvents::PAYPAL_CUSTOMER_UPDATE, $payPalCustomerEvent);

            /** @var \Thelia\Model\Address $deliveryAddress */
            $deliveryAddress = $cart->getCustomer()->getDefaultAddress();

            /** @var \Thelia\Model\Module $deliveryModule */
            $deliveryModule = ModuleQuery::create()->filterByActivate(1)->findOne();
            /** @var \Thelia\Model\Module $paymentModule */
            $paymentModule = ModuleQuery::create()->findPk(PayPal::getModuleId());

            /** @var \Thelia\Model\Currency $currency */
            $currency = $cart->getCurrency();
            $lang = $this->getRequest()->getSession()->getLang();

            $order = new Order();
            $order
                ->setCustomerId($cart->getCustomerId())
                ->setCurrencyId($currency->getId())
                ->setCurrencyRate($currency->getRate())
                ->setStatusId(OrderStatusQuery::getNotPaidStatus()->getId())
                ->setLangId($lang->getDefaultLanguage()->getId())
                ->setChoosenDeliveryAddress($deliveryAddress)
                ->setChoosenInvoiceAddress($invoiceAddress)
            ;

            $orderEvent = new OrderEvent($order);

            /* get postage amount */
            $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);
            $deliveryPostageEvent = new DeliveryPostageEvent($moduleInstance, $cart, $deliveryAddress);

            $this->getDispatcher()->dispatch(
                TheliaEvents::MODULE_DELIVERY_GET_POSTAGE,
                $deliveryPostageEvent
            );

            if (!$deliveryPostageEvent->isValidModule() || null === $deliveryPostageEvent->getPostage()) {
                throw new DeliveryException(
                    $this->getTranslator()->trans('The delivery module is not valid.', [], PayPal::DOMAIN_NAME)
                );
            }

            $postage = $deliveryPostageEvent->getPostage();

            $orderEvent->setPostage($postage->getAmount());
            $orderEvent->setPostageTax($postage->getAmountTax());
            $orderEvent->setPostageTaxRuleTitle($postage->getTaxRuleTitle());
            $orderEvent->setDeliveryAddress($deliveryAddress->getId());
            $orderEvent->setInvoiceAddress($invoiceAddress->getId());
            $orderEvent->setDeliveryModule($deliveryModule->getId());
            $orderEvent->setPaymentModule($paymentModule->getId());

            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_SET_DELIVERY_ADDRESS, $orderEvent);
            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_SET_INVOICE_ADDRESS, $orderEvent);
            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_SET_POSTAGE, $orderEvent);
            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_SET_DELIVERY_MODULE, $orderEvent);
            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_SET_PAYMENT_MODULE, $orderEvent);

            $orderManualEvent = new OrderManualEvent(
                $orderEvent->getOrder(),
                $orderEvent->getOrder()->getCurrency(),
                $orderEvent->getOrder()->getLang(),
                $cart,
                $cart->getCustomer()
            );

            $this->getDispatcher()->dispatch(TheliaEvents::ORDER_CREATE_MANUAL, $orderManualEvent);
            $order = $orderManualEvent->getPlacedOrder();

            $payPalOrderEvent = $payPalPaymentService->generatePayPalOrder($order);
            $payPalPaymentService->updatePayPalOrder($payPalOrderEvent->getPayPalOrder(), $payment->getState(), $payment->getId());

            $response = $this->executePayment(
                $payPalOrderEvent->getPayPalOrder(),
                $payPalCart->getExpressPaymentId(),
                $payPalCart->getExpressPayerId(),
                $payPalCart->getExpressToken(),
                PayPal::PAYPAL_METHOD_EXPRESS_CHECKOUT,
                $payPalPaymentService->createDetails(
                    $order->getPostage(),
                    $order->getPostageTax(),
                    $order->getTotalAmount($tax, false)
                )
            );

            $con->commit();
        } catch (PayPalConnectionException $e) {
            $con->rollBack();

            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            $response = $responseParent;
        } catch(\Exception $e) {
            $con->rollBack();

            $customerId = null;
            if (isset($customer)) {
                $customerId = $customer->getId();
            }

            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $customerId
                ],
                Logger::CRITICAL
            );
            $response = $responseParent;
        }

        $con->commit();
        return $response;
    }

    /**
     *
     */
    public function expressCheckoutKoAction()
    {
        PayPalLoggerService::log(
            Translator::getInstance()->trans('Express Checkout login failed', [], PayPal::DOMAIN_NAME),
            [],
            Logger::WARNING
        );
        return $this->getUrlFromRouteId('cart.view');
    }

    /**
     * Method called when a customer log in with PayPal.
     * @return RedirectResponse
     * @throws \Exception
     */
    public function loginOkAction()
    {
        if (null !== $authorizationCode = $this->getRequest()->query->get('code')) {

            /** @var PayPalCustomerService $payPalCustomerService */
            $payPalCustomerService = $this->container->get(PayPal::PAYPAL_CUSTOMER_SERVICE_ID);
            $openIdUserinfo = $payPalCustomerService->getUserInfoWithAuthorizationCode($authorizationCode);

            $payPalCustomer = $payPalCustomerService->getCurrentPayPalCustomer();
            $payPalCustomer
                ->setPaypalUserId($openIdUserinfo->getUserId())
                ->setName($openIdUserinfo->getName())
                ->setGivenName($openIdUserinfo->getGivenName())
                ->setFamilyName($openIdUserinfo->getFamilyName())
                ->setMiddleName($openIdUserinfo->getMiddleName())
                ->setPicture($openIdUserinfo->getPicture())
                ->setEmailVerified($openIdUserinfo->getEmailVerified())
                ->setGender($openIdUserinfo->getGender())
                ->setBirthday($openIdUserinfo->getBirthday())
                ->setZoneinfo($openIdUserinfo->getZoneinfo())
                ->setLocale($openIdUserinfo->getLocale())
                ->setLanguage($openIdUserinfo->getLanguage())
                ->setVerified($openIdUserinfo->getVerified())
                ->setPhoneNumber($openIdUserinfo->getPhoneNumber())
                ->setVerifiedAccount($openIdUserinfo->getVerifiedAccount())
                ->setAccountType($openIdUserinfo->getAccountType())
                ->setAgeRange($openIdUserinfo->getAgeRange())
                ->setPayerId($openIdUserinfo->getPayerId())
                ->setPostalCode($openIdUserinfo->getAddress()->getPostalCode())
                ->setLocality($openIdUserinfo->getAddress()->getLocality())
                ->setRegion($openIdUserinfo->getAddress()->getRegion())
                ->setCountry($openIdUserinfo->getAddress()->getCountry())
                ->setStreetAddress($openIdUserinfo->getAddress()->getStreetAddress())
            ;

            $payPalCustomerEvent = new PayPalCustomerEvent($payPalCustomer);
            $this->dispatch(PayPalEvents::PAYPAL_CUSTOMER_UPDATE, $payPalCustomerEvent);

            $this->dispatch(TheliaEvents::CUSTOMER_LOGIN, new CustomerLoginEvent($payPalCustomerEvent->getPayPalCustomer()->getCustomer()));
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl($this->getSession()->getReturnToUrl()));
    }

    /**
     * @param $orderId
     * @return RedirectResponse
     */
    public function agreementOkAction($orderId)
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        $token = $this->getRequest()->query->get('token');
        $payPalOrder = PaypalOrderQuery::create()->findOneById($orderId);

        if (null !== $payPalOrder && null !== $token) {

            try {
                /** @var PayPalAgreementService $payPalAgreementService */
                $payPalAgreementService = $this->container->get(PayPal::PAYPAL_AGREEMENT_SERVICE_ID);
                $agreement = $payPalAgreementService->activateBillingAgreementByToken($token);

                $payPalOrder
                    ->setState($agreement->getState())
                    ->setAgreementId($agreement->getId())
                    ->setPayerId($agreement->getPayer()->getPayerInfo()->getPayerId())
                    ->setToken($token)
                ;
                $payPalOrderEvent = new PayPalOrderEvent($payPalOrder);
                $this->dispatch(PayPalEvents::PAYPAL_ORDER_UPDATE, $payPalOrderEvent);

                $event = new OrderEvent($payPalOrder->getOrder());
                $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
                $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

                $response = $this->getPaymentSuccessPageUrl($orderId);
                PayPalLoggerService::log(
                    Translator::getInstance()->trans(
                        'Order payed with success in PayPal with method : %method',
                        [
                            '%method' => PayPal::PAYPAL_METHOD_PLANIFIED_PAYMENT
                        ],
                        PayPal::DOMAIN_NAME
                    ),
                    [
                        'order_id' => $payPalOrder->getId(),
                        'customer_id' => $payPalOrder->getOrder()->getCustomerId()
                    ],
                    Logger::INFO
                );
            } catch (PayPalConnectionException $e) {
                $con->rollBack();
                $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
                PayPalLoggerService::log(
                    $message,
                    [
                        'customer_id' => $orderId
                    ],
                    Logger::CRITICAL
                );

                $response = $this->getPaymentFailurePageUrl($orderId, $e->getMessage());
            } catch (\Exception $e) {
                $con->rollBack();
                PayPalLoggerService::log(
                    $e->getMessage(),
                    [
                        'order_id' => $orderId
                    ],
                    Logger::CRITICAL
                );

                $response = $this->getPaymentFailurePageUrl($orderId, $e->getMessage());
            }

        } else {
            $con->rollBack();
            $message = Translator::getInstance()->trans(
                'Method agreementOkAction => One of this parameter is invalid : $token = %token, $orderId = %order_id',
                [
                    '%token' => $token,
                    '%order_id' => $orderId
                ],
                PayPal::DOMAIN_NAME
            );

            PayPalLoggerService::log(
                $message,
                [
                    'order_id' => $orderId
                ],
                Logger::CRITICAL
            );

            $response = $this->getPaymentFailurePageUrl($orderId, $message);
        }

        $con->commit();
        return $response;
    }

    /**
     * @param $orderId
     */
    public function ipnAction($orderId)
    {
        PayPalLoggerService::log('GUIGIT', ['hook' => 'guigit', 'order_id' => $orderId], Logger::DEBUG);

        PayPalLoggerService::log(
            print_r($this->getRequest()->request, true),
            [
                'hook' => 'guigit',
                'order_id' => $orderId
            ],
            Logger::DEBUG
        );
        PayPalLoggerService::log(
            print_r($this->getRequest()->attributes, true),
            [
                'hook' => 'guigit',
                'order_id' => $orderId
            ],
            Logger::DEBUG
        );
    }

    /**
     * Return the order payment success page URL
     *
     * @param $orderId
     * @return RedirectResponse
     */
    public function getPaymentSuccessPageUrl($orderId)
    {
        return $this->getUrlFromRouteId('order.placed', ['order_id' =>  $orderId]);
    }

    /**
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function fillCartWithExpressCheckout()
    {
        $paymentId = $this->getRequest()->get('paymentId');
        $token = $this->getRequest()->get('token');
        $payerId = $this->getRequest()->get('PayerID');
        $cartId = $this->getRequest()->get('cartId');
        $cart = CartQuery::create()->findOneById($this->getRequest()->get('cartId'));

        if (null === $paymentId || null === $token || null === $payerId || null === $cart) {
            PayPalLoggerService::log(
                Translator::getInstance()->trans('Express checkout failed in expressCheckoutOkAction() function', [], PayPal::DOMAIN_NAME),
                [],
                Logger::CRITICAL
            );
        }

        PayPalLoggerService::log(
            Translator::getInstance()->trans('Express checkout begin with cart %id', ['%id' => $cartId], PayPal::DOMAIN_NAME)
        );

        /** @var PayPalPaymentService $payPalPaymentService */
        $payPalPaymentService = $this->container->get(PayPal::PAYPAL_PAYMENT_SERVICE_ID);
        $payment = $payPalPaymentService->getPaymentDetails($paymentId);

        $payerInfo = $payment->getPayer()->getPayerInfo();
        if (null === $customer = CustomerQuery::create()->findOneByEmail($payment->getPayer()->getPayerInfo()->getEmail())) {

            $customerCreateEvent = $this->createEventInstance($payerInfo);

            $this->dispatch(TheliaEvents::CUSTOMER_CREATEACCOUNT, $customerCreateEvent);

            $customer = $customerCreateEvent->getCustomer();

        }

        //Save informations to use them after customer has choosen the delivery method
        if (null === $payPalCart = PaypalCartQuery::create()->findOneById($cartId)) {
            $payPalCart = new PaypalCart();
            $payPalCart->setId($cartId);
        }

        $payPalCart
            ->setExpressPaymentId($paymentId)
            ->setExpressPayerId($payerId)
            ->setExpressToken($token)
        ;
        $payPalCartEvent = new PayPalCartEvent($payPalCart);
        $this->getDispatcher()->dispatch(PayPalEvents::PAYPAL_CART_UPDATE, $payPalCartEvent);

        $cart->setCustomerId($customer->getId())->save();
        $clonedCart = clone $cart;
        $this->dispatch(TheliaEvents::CUSTOMER_LOGIN, new CustomerLoginEvent($customer));

        //In case of the current customer has changed, re affect the correct cart and customer session
        $this->getSecurityContext()->setCustomerUser($customer);
        $clonedCart->save();
        $this->getRequest()->getSession()->set("thelia.cart_id", $clonedCart->getId());
    }

    /**
     * @param $routeId
     * @param array $params
     * @return RedirectResponse
     */
    protected function getUrlFromRouteId($routeId, $params = [])
    {
        $frontOfficeRouter = $this->getContainer()->get('router.front');

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl(
                $frontOfficeRouter->generate(
                    $routeId,
                    $params,
                    Router::ABSOLUTE_URL
                )
            )
        );
    }

    /**
     * Redirect the customer to the failure payment page. if $message is null, a generic message is displayed.
     *
     * @param $orderId
     * @param $message
     * @return RedirectResponse
     */
    public function getPaymentFailurePageUrl($orderId, $message)
    {
        $frontOfficeRouter = $this->getContainer()->get('router.front');

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl(
                $frontOfficeRouter->generate(
                    "order.failed",
                    array(
                        "order_id" => $orderId,
                        "message" => $message
                    ),
                    Router::ABSOLUTE_URL
                )
            )
        );
    }

    /**
     * @param PaypalOrder $payPalOrder
     * @param $paymentId
     * @param $payerId
     * @param $token
     * @param string $method
     * @param Details|null $details
     * @return RedirectResponse
     */
    protected function executePayment(PaypalOrder $payPalOrder, $paymentId, $payerId, $token, $method = PayPal::PAYPAL_METHOD_PAYPAL, Details $details = null)
    {
        /** @var PayPalPaymentService $payPalService */
        $payPalService = $this->getContainer()->get(PayPal::PAYPAL_PAYMENT_SERVICE_ID);
        $payment = $payPalService->executePayment($paymentId, $payerId, $details);

        $payPalOrder
            ->setState($payment->getState())
            ->setPayerId($payerId)
            ->setToken($token)
        ;
        $payPalOrderEvent = new PayPalOrderEvent($payPalOrder);
        $this->dispatch(PayPalEvents::PAYPAL_ORDER_UPDATE, $payPalOrderEvent);

        $event = new OrderEvent($payPalOrder->getOrder());
        $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
        $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

        $response = $this->getPaymentSuccessPageUrl($payPalOrder->getId());

        PayPalLoggerService::log(
            Translator::getInstance()->trans(
                'Order payed with success in PayPal with method : %method',
                [
                    '%method' => $method
                ],
                PayPal::DOMAIN_NAME
            ),
            [
                'order_id' => $payPalOrder->getId(),
                'customer_id' => $payPalOrder->getOrder()->getCustomerId()
            ],
            Logger::INFO
        );


        return $response;
    }

    /**
     * @param PayerInfo $payerInfo
     * @return \Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent
     */
    protected function createEventInstance(PayerInfo $payerInfo)
    {
        if (null === $country = CountryQuery::create()->findOneByIsoalpha2($payerInfo->getShippingAddress()->getCountryCode())) {
            $country = Country::getDefaultCountry();
        }

        $customerCreateEvent = new CustomerCreateOrUpdateEvent(
            CustomerTitleQuery::create()->findOne()->getId(),
            $payerInfo->getFirstName(),
            $payerInfo->getLastName(),
            $payerInfo->getShippingAddress()->getLine1(),
            $payerInfo->getShippingAddress()->getLine2(),
            null,
            $payerInfo->getPhone(),
            null,
            $payerInfo->getShippingAddress()->getPostalCode(),
            $payerInfo->getShippingAddress()->getCity(),
            $country->getId(),
            $payerInfo->getEmail(),
            'random',
            $this->getRequest()->getSession()->getLang()->getId(),
            null,
            null,
            null,
            null,
            null,
            null
        );

        return $customerCreateEvent;
    }

    /**
     * @param PayerInfo $payerInfo
     * @return AddressCreateOrUpdateEvent
     */
    protected function createAddressEvent(PayerInfo $payerInfo)
    {
        if (null !== $payerInfo->getBillingAddress()) {
            $countryCode = $payerInfo->getBillingAddress()->getCountryCode();
            $line1 = $payerInfo->getBillingAddress()->getLine1();
            $line2 = $payerInfo->getBillingAddress()->getLine2();
            $zipCode = $payerInfo->getBillingAddress()->getPostalCode();
            $city = $payerInfo->getBillingAddress()->getCity();
        } else {
            $countryCode = $payerInfo->getShippingAddress()->getCountryCode();
            $line1 = $payerInfo->getShippingAddress()->getLine1();
            $line2 = $payerInfo->getShippingAddress()->getLine2();
            $zipCode = $payerInfo->getShippingAddress()->getPostalCode();
            $city = $payerInfo->getShippingAddress()->getCity();
        }

        if (null === $country = CountryQuery::create()->findOneByIsoalpha2($countryCode)) {
            $country = Country::getDefaultCountry();
        }

        return new AddressCreateOrUpdateEvent(
            'Express checkout PayPal',
            CustomerTitleQuery::create()->findOne()->getId(),
            $payerInfo->getFirstName(),
            $payerInfo->getLastName(),
            $line1,
            ($line2)?$line2:'',
            '',
            $zipCode,
            $city,
            $country->getId(),
            $payerInfo->getPhone(),
            $payerInfo->getPhone(),
            '',
            0,
            null
        );
    }
}
