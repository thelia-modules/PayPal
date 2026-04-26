<?php

declare(strict_types=1);

namespace PayPal\EventListeners;

use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Api\Bridge\Propel\Event\PaymentModuleOptionEvent;
use Thelia\Api\Resource\PaymentModuleOption;
use Thelia\Api\Resource\PaymentModuleOptionGroup;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Order;

class PaymentModuleOptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::MODULE_PAYMENT_GET_OPTIONS => ['getPaymentModuleOptions', 128],
        ];
    }

    public function getPaymentModuleOptions(PaymentModuleOptionEvent $event): void
    {
        if ($event->getModule()->getId() !== PayPal::getModuleId()) {
            return;
        }

        if (!PayPal::getConfigValue('method_planified_payment')) {
            return;
        }

        $session = $this->requestStack->getSession();
        /** @var \Thelia\Model\Lang $lang */
        $lang = $session->getLang();

        /** @var Cart $cart */
        $cart = $session->getSessionCart($this->dispatcher);

        /** @var Order|null $order */
        $order = $session->get('thelia.order');

        $country = Country::getDefaultCountry();

        $planifiedPayments = (new PaypalPlanifiedPaymentQuery())->joinWithI18n($lang->getLocale())->find();

        $paymentModuleOptionGroup = (new PaymentModuleOptionGroup())
            ->setCode('paypal_type')
            ->setTitle(Translator::getInstance()->trans('Choose a payment option', [], PayPal::DOMAIN_NAME))
            ->setDescription('')
            ->setMinimumSelectedOptions(1)
            ->setMaximumSelectedOptions(1);

        $option = (new PaymentModuleOption())
            ->setCode('paypal')
            ->setTitle(Translator::getInstance()->trans('Paypal', [], PayPal::DOMAIN_NAME))
            ->setDescription(Translator::getInstance()->trans('Paiement direct avec Paypal', [], PayPal::DOMAIN_NAME));

        $paymentModuleOptionGroup->appendPaymentModuleOption($option);

        if (null === $cart || null === $order || null === $country) {
            return;
        }

        $totalAmount = $cart->getTaxedAmount($country) + (float) $order->getPostage();

        /** @var PaypalPlanifiedPayment $planifiedPayment */
        foreach ($planifiedPayments as $planifiedPayment) {
            if ($planifiedPayment->getMinAmount() > 0 && $planifiedPayment->getMinAmount() > $totalAmount) {
                continue;
            }

            if ($planifiedPayment->getMaxAmount() > 0 && $planifiedPayment->getMaxAmount() < $totalAmount) {
                continue;
            }

            $option = (new PaymentModuleOption())
                ->setCode('PaypalPlanifiedPayment_'.$planifiedPayment->getId())
                ->setTitle((string) $planifiedPayment->getTitle())
                ->setDescription((string) $planifiedPayment->getDescription());

            $paymentModuleOptionGroup->appendPaymentModuleOption($option);
        }

        $event->appendPaymentModuleOptionGroups($paymentModuleOptionGroup);
    }
}
