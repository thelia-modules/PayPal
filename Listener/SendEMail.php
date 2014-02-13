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

namespace Paypal\Listener;

use Paypal\Paypal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Mailer\MailerFactory;
use Thelia\Core\Template\ParserInterface;
use Thelia\Model\ConfigQuery;
use Thelia\Model\MessageQuery;
/**
 * Class SendEMail
 * @package IciRelais\Listener
 * @author Thelia <info@thelia.net>
 */
class SendEMail extends BaseAction implements EventSubscriberInterface
{

    /**
     * @var MailerFactory
     */
    protected $mailer;
    /**
     * @var ParserInterface
     */
    protected $parser;

    function __construct(ParserInterface $parser,MailerFactory $mailer)
    {
        $this->parser = $parser;
        $this->mailer = $mailer;
    }

    /**
     * @return \Thelia\Mailer\MailerFactory
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /*
     * @params OrderEvent $order
     * Checks if order payment module is paypal and if order new status is paid, send an email to the customer.
     */
    public function update_status(OrderEvent $event) {
        if($event->getOrder()->getPaymentModuleId() === Paypal::getModCode()) {
            if($event->getOrder()->getStatusId() === Paypal::STATUS_PAID ) {

                $contact_email = ConfigQuery::read('store_email');

                if ($contact_email) {
                    $message = MessageQuery::create()
                        ->filterByName('payment_confirmation_paypal')
                        ->findOne();

                    if (false === $message) {
                        throw new \Exception("Failed to load message 'payment_confirmation_paypal'.");
                    }

                    $order = $event->getOrder();
                    $customer = $order->getCustomer();

                    $this->parser->assign('order_id', $order->getId());
                    $this->parser->assign('order_ref', $order->getRef());

                    $message
                        ->setLocale($order->getLang()->getLocale());

                    $instance = \Swift_Message::newInstance()
                        ->addTo($customer->getEmail(), $customer->getFirstname()." ".$customer->getLastname())
                        ->addFrom($contact_email, ConfigQuery::read('store_name'))
                    ;

                    // Build subject and body
                    $message->buildMessage($this->parser, $instance);

                    $this->getMailer()->send($instance);

                }
            }
        }

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::ORDER_UPDATE_STATUS => array("update_status", 128)
        );
    }

} 