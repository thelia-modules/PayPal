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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PayPalCartListener
 * @package PayPal\EventListeners
 */
class PayPalCartListener implements EventSubscriberInterface
{
    /**
     * @param PayPalCartEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function createOrUpdate(PayPalCartEvent $event)
    {
        $event->getPayPalCart()->save();
    }

    /**
     * @param PayPalCartEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(PayPalCartEvent $event)
    {
        $event->getPayPalCart()->delete();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalEvents::PAYPAL_CART_CREATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_CART_UPDATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_CART_DELETE => ['delete', 128]
        ];
    }
}
