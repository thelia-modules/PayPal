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

namespace PayPal\Hook;

use PayPal\Model\PaypalCartQuery;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\HttpFoundation\Session\Session;


/**
 * Class FrontHookManager
 * @package PayPal\Hook
 */
class FrontHookManager extends BaseHook
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var ContainerInterface */
    protected $container;

    /**
     * FrontHookManager constructor.
     * @param RequestStack $requestStack
     * @param ContainerInterface $container
     */
    public function __construct(RequestStack $requestStack, ContainerInterface $container)
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onLoginMainBottom(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();
        $templateData['paypal_appid'] = PayPalBaseService::getLogin();
        $templateData['paypal_authend'] = PayPalBaseService::getMode();

        $event->add(
            $this->render(
                'paypal/login-bottom.html',
                $templateData
            )
        );
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderInvoicePaymentExtra(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();
        $templateData['method_paypal_with_in_context'] = PayPal::getConfigValue('method_paypal_with_in_context');
        $event->add(
            $this->render(
                'paypal/order-invoice-payment-extra.html',
                $templateData
            )
        );
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderInvoiceBottom(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();
        $templateData['paypal_mode'] = PayPalBaseService::getMode();
        $templateData['paypal_merchantid'] = PayPalBaseService::getMerchantId();

        $event->add(
            $this->render(
                'paypal/order-invoice-bottom.html',
                $templateData
            )
        );
    }

    public function onOrderInvoiceJavascriptInitialization(HookRenderEvent $event)
    {
        $render = $this->render(
            'paypal/order-invoice-js.html',
            [
                'module_id' => PayPal::getModuleId(),
            ]
        );

        $event->add($render);
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderPlacedAdditionalPaymentInfo(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();
        $event->add(
            $this->render(
                'paypal/order-placed-additional-payment-info.html',
                $templateData
            )
        );
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onCartBottom(HookRenderEvent $event)
    {
        $payPal = new PayPal();
        $payPal->setContainer($this->container);

        if (PayPal::getConfigValue('method_express_checkout') == 1 && $payPal->isValidPayment()) {
            $templateData = $event->getArguments();
            $templateData['paypal_mode'] = PayPalBaseService::getMode();
            $templateData['paypal_merchantid'] = PayPalBaseService::getMerchantId();
            $event->add(
                $this->render(
                    'paypal/cart-bottom.html',
                    $templateData
                )
            );
        }
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderDeliveryFormBottom(HookRenderEvent $event)
    {
        if ($this->isValidExpressCheckout()) {
            $templateData = $event->getArguments();
            $event->add(
                $this->render(
                    'paypal/order-delivery-bottom.html',
                    $templateData
                )
            );
        }
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderAfterJavascriptInclude(HookRenderEvent $event)
    {
        if ($this->isValidExpressCheckout()) {
            $templateData = $event->getArguments();
            $event->add(
                $this->render(
                    'paypal/order-delivery-bottom-js.html',
                    $templateData
                )
            );
        }
    }

    protected function isValidExpressCheckout()
    {
        $isValid = false;

        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $cart = $session->getSessionCart($this->dispatcher);

        $payPal = new PayPal();
        $payPal->setContainer($this->container);

        if (PayPal::getConfigValue('method_express_checkout') == 1 && $payPal->isValidPayment()) {
            if (null !== $payPalCart = PaypalCartQuery::create()->findOneById($cart->getId())) {
                if ($payPalCart->getExpressPaymentId() && $payPalCart->getExpressPayerId() && $payPalCart->getExpressToken()) {
                    $isValid = true;
                }
            }
        }

        return $isValid;
    }
}
