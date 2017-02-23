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

use PayPal\Event\PayPalCustomerEvent;
use PayPal\Event\PayPalEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PayPalCustomerListener
 * @package PayPal\EventListeners
 */
class PayPalCustomerListener implements EventSubscriberInterface
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var EventDispatcher */
    protected $dispatcher;

    /**
     * PayPalCustomerListener constructor.
     * @param RequestStack $requestStack
     * @param EventDispatcher $dispatcher
     */
    public function __construct(RequestStack $requestStack, EventDispatcher $dispatcher)
    {
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param PayPalCustomerEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function createOrUpdate(PayPalCustomerEvent $event)
    {
        $event->getPayPalCustomer()->save();
    }

    /**
     * @param PayPalCustomerEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(PayPalCustomerEvent $event)
    {
        $event->getPayPalCustomer()->delete();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalEvents::PAYPAL_CUSTOMER_CREATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_CUSTOMER_UPDATE => ['createOrUpdate', 128],
            PayPalEvents::PAYPAL_CUSTOMER_DELETE => ['delete', 128]
        ];
    }
}
