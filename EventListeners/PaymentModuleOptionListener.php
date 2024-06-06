<?php

namespace PayPal\EventListeners;


use OpenApi\Events\OpenApiEvents;
use OpenApi\Events\PaymentModuleOptionEvent;
use OpenApi\Model\Api\ModelFactory;
use OpenApi\Model\Api\PaymentModuleOption;
use OpenApi\Model\Api\PaymentModuleOptionGroup;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Order;

class PaymentModuleOptionListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EventDispatcherInterface $dispatcher,
        private ModelFactory $modelFactory

    )
    {
    }

    public static function getSubscribedEvents()
    {
        $listenedEvents = [];

        if (class_exists(PaymentModuleOptionEvent::class)) {
            $listenedEvents[OpenApiEvents::MODULE_PAYMENT_GET_OPTIONS] = ["getPaymentModuleOptions", 128];
        }

        return $listenedEvents;
    }

    public function getPaymentModuleOptions(PaymentModuleOptionEvent $event)
    {
        if ($event->getModule()->getId() !== PayPal::getModuleId()) {
            return ;
        }

        if (PayPal::getConfigValue('method_planified_payment')) {
            $session = $this->requestStack->getSession();
            /** @var \Thelia\Model\Lang $lang */
            $lang = $session->getLang();

            /** @var Cart $cart */
            $cart = $session->getSessionCart($this->dispatcher);

            /** @var Order $order */
            $order = $session->get('thelia.order');

            $country = Country::getDefaultCountry();

            $planifiedPayments = (new PaypalPlanifiedPaymentQuery())->joinWithI18n($lang->getLocale())->find();

            /** @var PaymentModuleOptionGroup $paymentModuleOptionGroup */
            $paymentModuleOptionGroup = $this->modelFactory->buildModel('PaymentModuleOptionGroup');
            $paymentModuleOptionGroup
                ->setCode('paypal_type')
                ->setTitle(Translator::getInstance()->trans('Choose a payment option', [], PayPal::DOMAIN_NAME))
                ->setDescription('')
                ->setMinimumSelectedOptions(1)
                ->setMaximumSelectedOptions(1);

            /** @var PaymentModuleOption $option */
            $option = $this->modelFactory->buildModel('PaymentModuleOption');
            $option->setCode("paypal");
            $option->setTitle((Translator::getInstance()->trans("Paypal", [], PayPal::DOMAIN_NAME)));
            $option->setDescription(Translator::getInstance()->trans("Paiement direct avec Paypal", [], PayPal::DOMAIN_NAME));

            $paymentModuleOptionGroup->appendPaymentModuleOption($option);

            if (null !== $cart && null !== $order && null !== $country) {
                $totalAmount = $cart->getTaxedAmount($country) + (float)$order->getPostage();

                /** @var PaypalPlanifiedPayment $planifiedPayment */
                foreach ($planifiedPayments as $planifiedPayment) {

                    if ($planifiedPayment->getMinAmount() > 0 && $planifiedPayment->getMinAmount() > $totalAmount) {
                        continue;
                    }

                    if ($planifiedPayment->getMaxAmount() > 0 && $planifiedPayment->getMaxAmount() < $totalAmount) {
                        continue;
                    }

                    /** @var PaymentModuleOption $option */
                    $option = $this->modelFactory->buildModel('PaymentModuleOption');
                    $option->setCode("PaypalPlanifiedPayment_".$planifiedPayment->getId());
                    $option->setTitle($planifiedPayment->getTitle());
                    $option->setDescription($planifiedPayment->getDescription());

                    $paymentModuleOptionGroup->appendPaymentModuleOption($option);

                }
                $event->appendPaymentModuleOptionGroups($paymentModuleOptionGroup);
            }
        }
    }
}