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
use PayPal\Event\PayPalOrderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class PayPalOrderListener
 * @package PayPal\EventListeners
 */
class PayPalOrderListener implements EventSubscriberInterface
{
    /**
     * @param PayPalOrderEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function createOrUpdate(PayPalOrderEvent $event)
    {
        $event->getPayPalOrder()->save();
    }

    /**
     * @param PayPalOrderEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(PayPalOrderEvent $event)
    {
        $event->getPayPalOrder()->delete();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalEvents::PAYPAL_ORDER_CREATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_ORDER_UPDATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_ORDER_DELETE => ['delete', 128]
        ];
    }
}
