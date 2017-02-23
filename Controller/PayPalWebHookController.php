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

namespace PayPal\Controller;

use Monolog\Logger;
use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Model\PaypalOrderQuery;
use PayPal\Model\PaypalPlanQuery;
use PayPal\PayPal;
use PayPal\Service\PayPalAgreementService;
use PayPal\Service\PayPalLoggerService;
use Propel\Runtime\Propel;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\OrderStatusQuery;

/**
 * Class PayPalWebHookController
 * @package PayPal\Controller
 */
class PayPalWebHookController extends BaseFrontController
{
    const HOOK_BILLING_PLAN_CREATED = 'BILLING.PLAN.CREATED';
    const HOOK_BILLING_PLAN_UPDATED = 'BILLING.PLAN.UPDATED';
    const HOOK_BILLING_SUBSCRIPTION_CREATED = 'BILLING.SUBSCRIPTION.CREATED';

    const HOOK_PAYMENT_SALE_COMPLETED = 'PAYMENT.SALE.COMPLETED';
    const HOOK_PAYMENT_SALE_DENIED = 'PAYMENT.SALE.DENIED';

    //Classic PayPal payment
    const RESOURCE_TYPE_SALE = 'sale';

    //Planified payment
    const RESOURCE_TYPE_PLAN = 'plan';
    const RESOURCE_TYPE_AGREEMENT = 'agreement';

    /**
     * Example of array received in posted params :
     *
     *
     *   Array (
     *       'id' => 'WH-0LU96374794024348-4WG31854RU4949452',
     *       'event_version' => 1.0,
     *       'create_time' => '2017-02-03T15:31:29Z',
     *       'resource_type' => 'plan',
     *       'event_type' => 'BILLING.PLAN.CREATED',
     *       'summary' => 'A billing plan was created',
     *       'resource' => Array (
     *           'merchant_preferences' => Array (
     *               'setup_fee' => Array (
     *                   'currency' => 'EUR',
     *                   'value' => 0
     *               ),
     *               'return_url' => 'http://25b3ee89.ngrok.io/thelia_2_3_3/web/module/paypal/agreement/ok/208',
     *               'cancel_url' => 'http://25b3ee89.ngrok.io/thelia_2_3_3/web/module/paypal/agreement/ko/208',
     *               'auto_bill_amount' => 'NO',
     *               'initial_fail_amount_action' => 'CONTINUE',
     *               'max_fail_attempts' => 0
     *           ),
     *           'update_time' => '2017-02-03T15:31:29.348Z',
     *           'create_time' => '2017-02-03T15:31:29.348Z',
     *           'name' => 'plan for order 208',
     *           'description' => false,
     *           'links' => Array (
     *               0 => Array (
     *                   'href' => 'api.sandbox.paypal.com/v1/payments/billing-plans/P-2DV20774VJ3968037ASNA3RA',
     *                   'rel' => 'self',
     *                   'method' => 'GET'
     *               )
     *           ),
     *           'payment_definitions' => Array (
     *               0 => Array (
     *                   'name' => 'payment definition for order 208',
     *                   'type' => 'REGULAR',
     *                   'frequency' => 'Day',
     *                   'frequency_interval' => 1,
     *                   'amount' => Array (
     *                       'currency' => 'EUR',
     *                       'value' => 3.9
     *                   ),
     *                   'cycles' => 5,
     *                   'charge_models' => Array (
     *                       0 => Array (
     *                           'type' => 'SHIPPING',
     *                           'amount' => Array (
     *                               'currency' => 'EUR',
     *                               'value' => 0
     *                           ),
     *                           'id' => 'CHM-26B03456D8799461GASNA3RA'
     *                       )
     *                   ),
     *                   'id' => 'PD-3FB00313143031422ASNA3RA'
     *               )
     *           ),
     *           'id' => 'P-2DV20774VJ3968037ASNA3RA',
     *           'state' => 'CREATED',
     *           'type' => 'FIXED'
     *       ),
     *       'links' => Array (
     *           0 => Array (
     *               'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-0LU96374794024348-4WG31854RU4949452',
     *               'rel' => 'self',
     *               'method' => 'GET'
     *           ),
     *           1 => Array (
     *               'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-0LU96374794024348-4WG31854RU4949452/resend',
     *               'rel' => 'resend',
     *               'method' => 'POST'
     *           )
     *       )
     *   );
     */
    public function allAction()
    {
        $eventType = $this->getRequest()->request->get('event_type');
        $resource = $this->getRequest()->request->get('resource');
        $resourceType = $this->getRequest()->request->get('resource_type');

        $details = [
            'request' => $this->getRequest()->request->all()
        ];

        $params = [
            'hook' => $eventType
        ];

        $con = Propel::getConnection();
        $con->beginTransaction();

        try {

            $title = $this->getTitle($this->getRequest());

            if (is_array($resource)) {

                switch (strtolower($resourceType)) {

                    case self::RESOURCE_TYPE_SALE:
                        if (isset($resource['parent_payment'])) {
                            $params = $this->getParamsForSale($resource['parent_payment'], $params, $eventType);
                        }
                        if (isset($resource['billing_agreement_id'])) {
                            $params = $this->getParamsForAgreement($resource['billing_agreement_id'], $params);
                        }
                        break;

                    case self::RESOURCE_TYPE_PLAN:
                        if (isset($resource['id'])) {
                            $params = $this->getParamsForPlan($resource['id'], $params);
                        }
                        break;

                    case self::RESOURCE_TYPE_AGREEMENT:
                        if (isset($resource['id'])) {
                            $params = $this->getParamsForAgreement($resource['id'], $params);
                        }
                        break;

                    default:
                        break;
                }
            }

            PayPalLoggerService::log(
                '<h3>' . $title . '</h3>' . $this->printRecursiveData($details),
                $params,
                Logger::INFO
            );

            $con->commit();
        } catch (PayPalConnectionException $e) {

            $con->rollBack();
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log($message, $params, Logger::CRITICAL);
            PayPalLoggerService::log($this->printRecursiveData($this->getRequest()->request), $params, Logger::CRITICAL);

        } catch (\Exception $e) {

            $con->rollBack();
            PayPalLoggerService::log($e->getMessage(), $params, Logger::CRITICAL);
            PayPalLoggerService::log($this->printRecursiveData($this->getRequest()->request), $params, Logger::CRITICAL);

        }
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getTitle(Request $request)
    {
        $summary = $request->request->get('summary');

        $title = '';
        if (null !== $request->get('event_type')) {
            $title .= $request->get('event_type') . ' : ';
        }
        $title .= $summary;

        return $title;
    }

    /**
     * @param null $paymentId
     * @param array $params
     * @param null $eventType
     * @return array
     */
    protected function getParamsForSale($paymentId = null, $params = [], $eventType = null)
    {
        if (null !== $payPalOrder = PaypalOrderQuery::create()->findOneByPaymentId($paymentId)) {
            $params['order_id'] = $payPalOrder->getId();
            $params['customer_id'] = $payPalOrder->getOrder()->getCustomerId();

            if ($eventType === self::HOOK_PAYMENT_SALE_DENIED) {
                $event = new OrderEvent($payPalOrder->getOrder());
                $event->setStatus(OrderStatusQuery::getCancelledStatus()->getId());
                $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);
            }
        }

        return $params;
    }

    /**
     * @param null $planId
     * @param array $params
     * @return array
     */
    protected function getParamsForPlan($planId = null, $params = [])
    {
        if (null !== $payPalPlan = PaypalPlanQuery::create()->findOneByPlanId($planId)) {

            $params['order_id'] = $payPalPlan->getPaypalOrderId();
            $params['customer_id'] = $payPalPlan->getPaypalOrder()->getOrder()->getCustomerId();

        }

        return $params;
    }

    /**
     * @param null $agreementId
     * @param array $params
     * @return array
     */
    protected function getParamsForAgreement($agreementId = null, $params = [])
    {
        if (null !== $payPalOrder = PaypalOrderQuery::create()->filterByAgreementId($agreementId)->orderById()->findOne()) {

            // Do not duplicate order for the first PayPal payment because order has just been created.
            // We will duplicate this order for the next PayPal payment :)
            if ($payPalOrder->getPlanifiedActualCycle() > 0) {
                $params['order_id'] = $payPalOrder->getId();
                $params['customer_id'] = $payPalOrder->getOrder()->getCustomerId();

                /** @var PayPalAgreementService $payPalAgreementService */
                $payPalAgreementService = $this->container->get(PayPal::PAYPAL_AGREEMENT_SERVICE_ID);
                $newOrder = $payPalAgreementService->duplicateOrder($payPalOrder->getOrder());

                Translator::getInstance()->trans(
                    'New recursive invoice from order %id',
                    ['%id' => $payPalOrder->getId()],
                    PayPal::DOMAIN_NAME
                );

                PayPalLoggerService::log(
                    '<h3>New recursive invoice from order ' . $payPalOrder->getId() . '</h3>',
                    [
                        'order_id' => $newOrder->getId(),
                        'customer_id' => $payPalOrder->getOrder()->getCustomerId()
                    ],
                    Logger::INFO
                );
            }

            $payPalOrder->setPlanifiedActualCycle($payPalOrder->getPlanifiedActualCycle() + 1);
            $payPalOrderEvent = new PayPalOrderEvent($payPalOrder);
            $this->getDispatcher()->dispatch(PayPalEvents::PAYPAL_ORDER_UPDATE, $payPalOrderEvent);
        }

        return $params;
    }

    /**
     * @param array $data
     * @param int $deep
     * @return string
     */
    protected function printRecursiveData($data = [], $deep = 0)
    {
        $formatedString = '';
        foreach ($data as $key => $value) {

            for ($i = 0; $i <= $deep; $i++) {
                $formatedString .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            if (is_array($value)) {
                $formatedString .= '<strong>' . $key . '&nbsp;:&nbsp;</strong><br />' . $this->printRecursiveData($value, $deep + 1);
            } else {
                $formatedString .= '<strong>' . $key . '&nbsp;:&nbsp;</strong>' . $value . '<br />';
            }

        }

        return $formatedString;
    }
}
