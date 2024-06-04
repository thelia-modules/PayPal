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
use PayPal\Event\PayPalPlanifiedPaymentEvent;
use PayPal\PayPal;
use PayPal\Service\PayPalApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Log\Tlog;

class PayPalPlanifiedPaymentListener implements EventSubscriberInterface
{
    public function __construct(private PayPalApiService $payPalApiService)
    {
    }

    /**
     * @param PayPalPlanifiedPaymentEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function create(PayPalPlanifiedPaymentEvent $event)
    {
        $planifiedPayment =  $event->getPayPalPlanifiedPayment();
        $planifiedPayment->save();

        try {
            $body = [
                "name" => $planifiedPayment->getTitle(),
                "description" => $planifiedPayment->getDescription(),
                "type" => "SERVICE",
            ];

            $response = $this->payPalApiService->sendPostResquest($body, PayPal::getBaseUrl() . PayPal::PAYPAL_API_CREATE_PRODUCT_URL);
            $responseContent = $response->getContent();
            $responseInfo = json_decode($responseContent, true);
            $planifiedPayment->setPaypalId($responseInfo['id'])->save();
        }catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());
        }
    }

    public function update(PayPalPlanifiedPaymentEvent $event)
    {
        $event->getPayPalPlanifiedPayment()->save();
    }

    /**
     * @param PayPalPlanifiedPaymentEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(PayPalPlanifiedPaymentEvent $event)
    {
        $event->getPayPalPlanifiedPayment()->delete();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_CREATE => ['create', 128],
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_UPDATE => ['update', 128],
            PayPalEvents::PAYPAL_PLANIFIED_PAYMENT_DELETE => ['delete', 128]
        ];
    }
}
