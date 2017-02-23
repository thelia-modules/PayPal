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

use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalPlanEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalPlanListener implements EventSubscriberInterface
{
    /**
     * @param PayPalPlanEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function createOrUpdate(PayPalPlanEvent $event)
    {
        $event->getPayPalPlan()->save();
    }

    /**
     * @param PayPalPlanEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(PayPalPlanEvent $event)
    {
        $event->getPayPalPlan()->delete();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalEvents::PAYPAL_PLAN_CREATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_PLAN_UPDATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_PLAN_DELETE => ['delete', 128]
        ];
    }
}
