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

use PayPal\Form\ConfigurationForm;
use PayPal\Model\PaypalLogQuery;
use PayPal\Model\PaypalOrderQuery;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;

/**
 * Class BackHookManager
 * @package PayPal\Hook
 */
class BackHookManager extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher = null,
        ?\Thelia\Core\Template\Parser\ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [['type' => 'back', 'method' => 'onModuleConfigure']],
            'order-edit.payment-module-bottom' => [['type' => 'back', 'method' => 'onOrderEditPaymentModuleBottom']],
            'order.edit-js' => [['type' => 'back', 'method' => 'onOrderEditJs']],
        ];
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onModuleConfigure(HookRenderEvent $event)
    {
        $vars = [];
        if (null !== $moduleConfigs = ModuleConfigQuery::create()->findByModuleId(PayPal::getModuleId())) {
            /** @var ModuleConfig $moduleConfig */
            foreach ($moduleConfigs as $moduleConfig) {
                $vars[$moduleConfig->getName()] = $moduleConfig->getValue();
            }
        }

        $vars['paypal_appid'] = PayPalBaseService::getLogin();
        $vars['paypal_authend'] = PayPalBaseService::getMode();

        $form = $this->formFactory->createForm(ConfigurationForm::getName(), data: $vars);
        $vars['form'] = $form->createView()->getView();

        $event->add(
            $this->render('PayPal/module-configuration.html.twig', $vars)
        );
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderEditPaymentModuleBottom(HookRenderEvent $event)
    {
        $orderId = (int) $event->getArgument('order_id');

        if (null !== PaypalOrderQuery::create()->findOneById($orderId)) {
            $logs = PaypalLogQuery::create()
                ->filterByOrderId($orderId)
                ->orderByCreatedAt(Criteria::DESC)
                ->find();

            $event->add(
                $this->render(
                    'PayPal/payment-information.html.twig',
                    ['logs' => $logs]
                )
            );
        }
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderEditJs(HookRenderEvent $event)
    {
        $orderId = (int) $event->getArgument('order_id');

        if (null !== PaypalOrderQuery::create()->findOneById($orderId)) {
            $event->add(
                $this->render('PayPal/order-edit-js.html.twig', [])
            );
        }
    }
}
