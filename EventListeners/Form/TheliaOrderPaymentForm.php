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

namespace PayPal\EventListeners\Form;

use PayPal\Form\PayPalFormFields;
use PayPal\Form\Type\PayPalCreditCardType;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Order;

/**
 * Class TheliaOrderPaymentForm
 * @package PayPal\EventListeners\Form
 */
class TheliaOrderPaymentForm implements EventSubscriberInterface
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * TheliaOrderPaymentForm constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, EventDispatcherInterface $dispatcher)
    {
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param TheliaFormEvent $event
     */
    public function afterBuildTheliaOrderPayment(TheliaFormEvent $event)
    {
        $event->getForm()->getFormBuilder()
            ->add(
                PayPalFormFields::FIELD_PAYPAL_METHOD,
                'choice',
                [
                    'choices' => [
                        PayPal::PAYPAL_METHOD_PAYPAL => PayPal::PAYPAL_METHOD_PAYPAL,
                        PayPal::PAYPAL_METHOD_EXPRESS_CHECKOUT => PayPal::PAYPAL_METHOD_EXPRESS_CHECKOUT,
                        PayPal::PAYPAL_METHOD_CREDIT_CARD => PayPal::PAYPAL_METHOD_CREDIT_CARD,
                        PayPal::PAYPAL_METHOD_PLANIFIED_PAYMENT => PayPal::PAYPAL_METHOD_PLANIFIED_PAYMENT
                    ],
                    'label' => Translator::getInstance()->trans('PayPal method', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PAYPAL_METHOD],
                    'required' => false,
                ]
            )
            ->add(
                PayPalCreditCardType::TYPE_NAME,
                new PayPalCreditCardType(),
                [
                    'label_attr' => [
                        'for' => PayPalCreditCardType::TYPE_NAME
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PAYPAL_PLANIFIED_PAYMENT,
                'choice',
                [
                    'choices' => $this->getAllowedPlanifiedPayments(),
                    'choices_as_values' => true,
                    'choice_label' => function ($value, $key, $index) {
                        return $value->getTitle();
                    },
                    'choice_value' => function ($value) {
                        if ($value !== null) {
                            return $value->getId();
                        }

                        return null;
                    },
                    "required" => false,
                    'empty_data'  => null,
                    'label' => Translator::getInstance()->trans('Frequency', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PAYPAL_PLANIFIED_PAYMENT],
                ]
            )
        ;
    }

    /**
     * @return array|mixed|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getAllowedPlanifiedPayments()
    {
        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()->getSession();

        /** @var \Thelia\Model\Lang $lang */
        $lang = $session->getLang();

        /** @var Cart $cart */
        $cart = $session->getSessionCart($this->dispatcher);

        /** @var Order $order */
        $order = $session->get('thelia.order');

        $country = Country::getDefaultCountry();

        $planifiedPayments = (new PaypalPlanifiedPaymentQuery())->joinWithI18n($lang->getLocale())->find();
        if (null !== $cart && null !== $order && null !== $country) {
            $totalAmount = $cart->getTaxedAmount($country) + (float)$order->getPostage();

            $restrictedPlanifiedAmounts = [];
            /** @var PaypalPlanifiedPayment $planifiedPayment */
            foreach ($planifiedPayments as $planifiedPayment) {

                if ($planifiedPayment->getMinAmount() > 0 && $planifiedPayment->getMinAmount() > $totalAmount) {
                    continue;
                }

                if ($planifiedPayment->getMaxAmount() > 0 && $planifiedPayment->getMaxAmount() < $totalAmount) {
                    continue;
                }

                $restrictedPlanifiedAmounts[] = $planifiedPayment;
            }

            $planifiedPayments = $restrictedPlanifiedAmounts;
        }

        return $planifiedPayments;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::FORM_AFTER_BUILD . '.thelia_order_payment' => ['afterBuildTheliaOrderPayment', 128]
        ];
    }
}
