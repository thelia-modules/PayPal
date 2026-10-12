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
use PayPal\Service\OrderOwnerGuard;
use PayPal\Service\PayPalAgreementService;
use PayPal\Service\PayPalCustomerService;
use PayPal\Service\PayPalLoggerService;
use PayPal\Service\PayPalPaymentService;
use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Order\OrderManualEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\SecurityContext;
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
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class PayPalResponseController
 * @package PayPal\Controller
 */
#[Route('', name: 'paypal')]
class PayPalResponseController extends BaseFrontController
{
    /**
     * @param $orderId
     * @param EventDispatcherInterface $eventDispatcher
     */
    #[Route('/module/paypal/cancel/{orderId}', name: '_cancel', methods: 'GET')]
    public function cancelAction($orderId, EventDispatcherInterface $eventDispatcher, OrderOwnerGuard $orderOwnerGuard)
    {
        $order = OrderQuery::create()->findOneById($orderId);

        if (!$orderOwnerGuard->isOwnedBy($order, $this->getSession()->getCustomerUser())) {
            return $this->pageNotFound();
        }

        // The buyer left PayPal without paying: back to the failure page of the theme, as every payment module of
        // the core does (BasePaymentModuleController::redirectToFailurePage()). Cancelling the order is that page's
        // job (Flexy: CheckoutFacade::cancelOrder(), an order of the session still waiting for its payment only):
        // cancelled here as well, the page would find nothing left to cancel and could not tell the buyer which
        // order failed, and a paid order reached by this address was cancelled with it.
        $orderId = $order->getId();
        $message = Translator::getInstance()->trans('Order cancel', [], PayPal::DOMAIN_NAME);

        return $this->getPaymentFailurePageUrl($orderId, $message);
    }

    /**
     * @param $orderId
     * @param RequestStack $requestStack
     * @param EventDispatcherInterface $eventDispatcher
     * @return RedirectResponse
     */
    #[Route('/module/paypal/ok/{orderId}', name: '_ok', methods: ['GET'])]
    public function okAction($orderId, RequestStack $requestStack, EventDispatcherInterface $eventDispatcher)
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        try {
            $request = $requestStack->getCurrentRequest();
            $payerId = $request->query->get('PayerID');
            $token = $request->query->get('token');
            $payPalOrder = PaypalOrderQuery::create()->findOneById($orderId);

            if (null !== $payPalOrder && null !== $payerId) {

                $response = $this->executePayment($eventDispatcher, $payPalOrder, $payPalOrder->getPaymentId(), $payerId, $token);
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
     * Method called when a customer log in with PayPal.
     * @param RequestStack $requestStack
     * @param EventDispatcherInterface $eventDispatcher
     * @return RedirectResponse
     * @throws \Propel\Runtime\Exception\PropelException
     */
    #[Route('/module/paypal/login/ok', name: '_login_ok', methods: ['GET'])]
    public function loginOkAction(RequestStack $requestStack, EventDispatcherInterface $eventDispatcher)
    {
        if (null !== $authorizationCode = $requestStack->getCurrentRequest()->query->get('code')) {

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
            $eventDispatcher->dispatch($payPalCustomerEvent, PayPalEvents::PAYPAL_CUSTOMER_UPDATE);

            $eventDispatcher->dispatch(new CustomerLoginEvent($payPalCustomerEvent->getPayPalCustomer()->getCustomer()), TheliaEvents::CUSTOMER_LOGIN);
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl($requestStack->getCurrentRequest()->getSession()->getReturnToUrl()));
    }

    /**
     */
    #[Route('/module/paypal/agreement/ok/{orderId}', name: '_agreement_ok', methods: ['GET'])]
    public function agreementOkAction($orderId, RequestStack $requestStack, EventDispatcherInterface $eventDispatcher)
    {
        $con = Propel::getConnection();
        $con->beginTransaction();

        $token = $requestStack->getCurrentRequest()->query->get('token');
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
                $eventDispatcher->dispatch($payPalOrderEvent, PayPalEvents::PAYPAL_ORDER_UPDATE);

                $event = new OrderEvent($payPalOrder->getOrder());
                $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
                $eventDispatcher->dispatch($event, TheliaEvents::ORDER_UPDATE_STATUS);

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
     */
    #[Route('/module/paypal/ipn/{orderId}', name: '_ipn', methods: ['GET'])]
    public function ipnAction($orderId, RequestStack $requestStack)
    {
        PayPalLoggerService::log('GUIGIT', ['hook' => 'guigit', 'order_id' => $orderId], Logger::DEBUG);

        PayPalLoggerService::log(
            print_r($requestStack->getCurrentRequest()->request, true),
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
        return $this->getUrlFromRouteId('checkout_confirm', ['order_id' =>  $orderId]);
    }

    /**
     * @param $routeId
     * @param array $params
     * @return RedirectResponse
     */
    protected function getUrlFromRouteId($routeId, $params = [])
    {
        $frontOfficeRouter = $this->getContainer()->get('router');

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
        $frontOfficeRouter = $this->getContainer()->get('router');

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl(
                $frontOfficeRouter->generate(
                    "checkout_failed",
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
    protected function executePayment(EventDispatcherInterface $eventDispatcher, PaypalOrder $payPalOrder, $paymentId, $payerId, $token, $method = PayPal::PAYPAL_METHOD_PAYPAL, Details $details = null)
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
        $eventDispatcher->dispatch($payPalOrderEvent, PayPalEvents::PAYPAL_ORDER_UPDATE);

        $event = new OrderEvent($payPalOrder->getOrder());
        $event->setStatus(OrderStatusQuery::getPaidStatus()->getId());
        $eventDispatcher->dispatch($event, TheliaEvents::ORDER_UPDATE_STATUS);

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

}
