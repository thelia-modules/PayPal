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
/**
 * Created by Franck Allimant, CQFDev <franck@cqfdev.fr>
 * Date: 11/01/2016 11:57
 */

namespace Paypal\Hook;

use Paypal\Classes\API\PaypalApiLogManager;
use Paypal\Paypal;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactoryInterface;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\ParserContext;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;

class HookManager extends BaseHook
{
    public function onModuleConfigure(HookRenderEvent $event)
    {
        $logFilePath = PaypalApiLogManager::getLogFilePath();

        $traces = @file_get_contents($logFilePath);

        if (false === $traces) {
            $traces = $this->translator->trans("Le fichier de log n'existe pas encore.", [], Paypal::DOMAIN);
        } elseif (empty($traces)) {
            $traces = $this->translator->trans("Le fichier de log est vide.", [], Paypal::DOMAIN);
        }

        $vars = ['trace_content' => nl2br($traces)  ];

        if (null !== $params = ModuleConfigQuery::create()->findByModuleId(Paypal::getModuleId())) {
            /** @var ModuleConfig $param */
            foreach ($params as $param) {
                $vars[ $param->getName() ] = $param->getValue();
            }
        }

        $event->add(
            $this->render('module-configuration.html', $vars)
        );
    }
}