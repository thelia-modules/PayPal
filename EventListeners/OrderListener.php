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

namespace PayPal\EventListeners;

use PayPal\Event\PayPalCartEvent;
use PayPal\Event\PayPalEvents;
use PayPal\Form\PayPalFormFields;
use PayPal\Form\Type\PayPalCreditCardType;
use PayPal\PayPal;
use PayPal\Service\PayPalAgreementService;
use PayPal\Service\PayPalPaymentService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Mailer\MailerFactory;

/**
 * Class OrderListener
 * @package PayPal\EventListeners
 */
class OrderListener implements EventSubscriberInterface
{
    /** @var MailerFactory */
    protected $mailer;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var RequestStack */
    protected $requestStack;

    /** @var PayPalPaymentService */
    protected $payPalPaymentService;

    /** @var PayPalAgreementService */
    protected $payPalAgreementService;

    /**
     * @param MailerFactory $mailer
     * @param EventDispatcherInterface $dispatcher
     * @param RequestStack $requestStack
     * @param PayPalPaymentService $payPalPaymentService
     * @param PayPalAgreementService $payPalAgreementService
     */
    public function __construct(MailerFactory $mailer, EventDispatcherInterface $dispatcher, RequestStack $requestStack, PayPalPaymentService $payPalPaymentService, PayPalAgreementService $payPalAgreementService)
    {
        $this->dispatcher = $dispatcher;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->payPalPaymentService = $payPalPaymentService;
        $this->payPalAgreementService = $payPalAgreementService;
    }

    /**
     * @param OrderEvent $event
     */
    public function CancelPayPalTransaction(OrderEvent $event)
    {
        // @TODO : Inform PayPal that this payment is canceled ?
    }

    /**
     * @param OrderEvent $event
     *
     * @throws \Exception if the message cannot be loaded.
     */
    public function sendConfirmationEmail(OrderEvent $event)
    {
        if (PayPal::getConfigValue('send_confirmation_message_only_if_paid')) {
            // We send the order confirmation email only if the order is paid
            $order = $event->getOrder();

            if (! $order->isPaid() && $order->getPaymentModuleId() == Paypal::getModuleId()) {
                $event->stopPropagation();
            }
        }
    }

    /**
     * Checks if order payment module is paypal and if order new status is paid, send an email to the customer.
     *
     * @param OrderEvent $event
     */
    public function updateStatus(OrderEvent $event)
    {
        $order = $event->getOrder();

        if ($order->isPaid() && $order->getPaymentModuleId() === Paypal::getModuleId()) {
            if (Paypal::getConfigValue('send_payment_confirmation_message')) {
                $this->mailer->sendEmailToCustomer(
                    PayPal::CONFIRMATION_MESSAGE_NAME,
                    $order->getCustomer(),
                    [
                        'order_id'  => $order->getId(),
                        'order_ref' => $order->getRef()
                    ]
                );
            }

            // Send confirmation email if required.
            if (Paypal::getConfigValue('send_confirmation_message_only_if_paid')) {
                $this->dispatcher->dispatch(TheliaEvents::ORDER_SEND_CONFIRMATION_EMAIL, $event);
            }
        }
    }

    /**
     * @param OrderEvent $event
     * @throws \Exception
     */
    public function checkPayPalMethod(OrderEvent $event)
    {
        //First be sure that there is no OLD CREDIT card saved in paypal_cart because of fatal error
        $payPalCartEvent = new PayPalCartEvent($this->payPalPaymentService->getCurrentPayPalCart());
        $this->dispatcher->dispatch(PayPalEvents::PAYPAL_CART_DELETE, $payPalCartEvent);

        $postedData = $this->requestStack->getCurrentRequest()->request->get('thelia_order_payment');

        if (isset($postedData[PayPalFormFields::FIELD_PAYMENT_MODULE]) && PayPal::getModuleId() === $event->getOrder()->getPaymentModuleId()) {
            $this->usePayPalMethod($postedData);
        }
    }

    /**
     * @param OrderEvent $event
     */
    public function recursivePayment(OrderEvent $event)
    {
        $this->payPalAgreementService->duplicateOrder($event->getOrder());

        if (PayPal::getConfigValue('send_recursive_message')) {
            $this->mailer->sendEmailToCustomer(
                PayPal::RECURSIVE_MESSAGE_NAME,
                $event->getOrder()->getCustomer(),
                [
                    'order_id'  => $event->getOrder()->getId(),
                    'order_ref' => $event->getOrder()->getRef()
                ]
            );
        }
    }

    /**
     * @param array $postedData
     */
    protected function usePayPalMethod($postedData = [])
    {
        if (isset($postedData[PayPalFormFields::FIELD_PAYPAL_METHOD])) {
            $payPalMethod = $postedData[PayPalFormFields::FIELD_PAYPAL_METHOD];

            switch ($payPalMethod) {
                case PayPal::PAYPAL_METHOD_CREDIT_CARD:
                    $this->usePayPalCreditCardMethod($postedData);
                    break;

                case PayPal::PAYPAL_METHOD_PLANIFIED_PAYMENT:
                    $this->usePayPalPlanifiedPaymentMethod($postedData);
                    break;
            }
        }
    }

    /**
     * @param array $postedData
     * @throws \Exception
     */
    protected function usePayPalCreditCardMethod($postedData = [])
    {
        if ($this->isValidPaidByPayPalCreditCard($postedData)) {
            //save credit card in cart because we will need it in pay() method for payment module

            $creditCardId = $this->payPalPaymentService->getPayPalCreditCardId(
                $postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_TYPE],
                $postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_NUMBER],
                $postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_MONTH],
                $postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_YEAR],
                $postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_CVV]
            );

            $payPalCart = $this->payPalPaymentService->getCurrentPayPalCart();
            $payPalCart->setCreditCardId($creditCardId);
            $payPalCartEvent = new PayPalCartEvent($payPalCart);
            $this->dispatcher->dispatch(PayPalEvents::PAYPAL_CART_UPDATE, $payPalCartEvent);
        }
    }

    /**
     * @param array $postedData
     * @return bool
     */
    protected function isValidPaidByPayPalCreditCard($postedData = [])
    {
        $isValid = false;

        if (isset($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_TYPE]) && $this->isNotBlank($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_TYPE]) &&
            isset($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_NUMBER]) && $this->isNotBlank($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_NUMBER]) &&
            isset($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_MONTH]) && $this->isNotBlank($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_MONTH]) &&
            isset($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_YEAR]) && $this->isNotBlank($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_EXPIRE_YEAR]) &&
            isset($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_CVV]) && $this->isNotBlank($postedData[PayPalCreditCardType::TYPE_NAME][PayPalFormFields::FIELD_CARD_CVV])) {
            $isValid = true;
        }

        return $isValid;
    }

    /**
     * @param array $postedData
     */
    protected function usePayPalPlanifiedPaymentMethod($postedData = [])
    {
        if (isset($postedData[PayPalFormFields::FIELD_PAYPAL_PLANIFIED_PAYMENT]) &&
            $this->isNotBlank($postedData[PayPalFormFields::FIELD_PAYPAL_PLANIFIED_PAYMENT])) {

            $payPalCart = $this->payPalPaymentService->getCurrentPayPalCart();
            $payPalCart->setPlanifiedPaymentId($postedData[PayPalFormFields::FIELD_PAYPAL_PLANIFIED_PAYMENT]);
            $payPalCartEvent = new PayPalCartEvent($payPalCart);
            $this->dispatcher->dispatch(PayPalEvents::PAYPAL_CART_UPDATE, $payPalCartEvent);

        }
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isNotBlank($value)
    {
        if (false === $value || (empty($value) && '0' != $value)) {
            return false;
        }

        return true;
    }
    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => [
                ['CancelPayPalTransaction', 128],
                ['updateStatus', 128],
            ],
            TheliaEvents::ORDER_SEND_CONFIRMATION_EMAIL => ['sendConfirmationEmail', 129],
            TheliaEvents::ORDER_SEND_NOTIFICATION_EMAIL => ['sendConfirmationEmail', 129],
            TheliaEvents::ORDER_SET_PAYMENT_MODULE => ['checkPayPalMethod', 120],
            PayPalEvents::PAYPAL_RECURSIVE_PAYMENT_CREATE => ['recursivePayment', 128]
        ];
    }
}
