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

use PayPal\Model\PaypalOrderQuery;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;


/**
 * Class BackHookManager
 * @package PayPal\Hook
 */
class BackHookManager extends BaseHook
{
    /**
     * @param HookRenderEvent $event
     */
    public function onModuleConfigure(HookRenderEvent $event)
    {
        $vars = [];
        if (null !== $moduleConfigs = ModuleConfigQuery::create()->findByModuleId(PayPal::getModuleId())) {
            /** @var ModuleConfig $moduleConfig */
            foreach ($moduleConfigs as $moduleConfig) {
                $vars[ $moduleConfig->getName() ] = $moduleConfig->getValue();
            }
        }

        $vars['paypal_appid'] = PayPalBaseService::getLogin();
        $vars['paypal_authend'] = PayPalBaseService::getMode();

        $event->add(
            $this->render('paypal/module-configuration.html', $vars)
        );
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderEditPaymentModuleBottom(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();

        if (null !== $payPalOrder = PaypalOrderQuery::create()->findOneById($event->getArgument('order_id'))) {
            $event->add(
                $this->render(
                    'paypal/payment-information.html',
                    $templateData
                )
            );
        }
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onOrderEditJs(HookRenderEvent $event)
    {
        $templateData = $event->getArguments();

        if (null !== $payPalOrder = PaypalOrderQuery::create()->findOneById($event->getArgument('order_id'))) {
            $event->add(
                $this->render(
                    'paypal/order-edit-js.html',
                    $templateData
                )
            );
        }
    }
}
