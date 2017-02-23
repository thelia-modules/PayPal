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

namespace PayPal\Service;

use Monolog\Logger;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\ChargeModel;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Currency;
use PayPal\Api\FundingInstrument;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Common\PayPalModel;
use PayPal\Event\PayPalEvents;
use PayPal\Event\PayPalOrderEvent;
use PayPal\Event\PayPalPlanEvent;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Model\PaypalOrder;
use PayPal\Model\PaypalOrderQuery;
use PayPal\Model\PaypalPlan;
use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanQuery;
use PayPal\PayPal;
use PayPal\Service\Base\PayPalBaseService;
use Symfony\Component\Routing\Router;
use Thelia\Core\Translation\Translator;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductQuery;
use Thelia\Model\OrderProductTax;
use Thelia\Model\OrderProductTaxQuery;
use Thelia\Tools\URL;

class PayPalAgreementService extends PayPalBaseService
{
    const PLAN_TYPE_FIXED = 'FIXED';
    const PLAN_TYPE_INFINITE = 'INFINITE';

    const PAYMENT_TYPE_REGULAR = 'REGULAR';
    const PAYMENT_TYPE_TRIAL = 'TRIAL';

    const CHARGE_TYPE_SHIPPING = 'SHIPPING';
    const CHARGE_TYPE_TAX = 'TAX';

    const PAYMENT_FREQUENCY_DAY = 'DAY';
    const PAYMENT_FREQUENCY_WEEK = 'WEEK';
    const PAYMENT_FREQUENCY_MONTH = 'MONTH';
    const PAYMENT_FREQUENCY_YEAR = 'YEAR';

    const FAIL_AMOUNT_ACTION_CONTINUE = 'CONTINUE';
    const FAIL_AMOUNT_ACTION_CANCEL = 'CANCEL';

    const MAX_API_LENGHT = 128;

    /**
     * @param Order $order
     * @param PaypalPlanifiedPayment $planifiedPayment
     * @param null $description
     * @return Agreement
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function makeAgreement(Order $order, PaypalPlanifiedPayment $planifiedPayment, $description = null)
    {
        //Sadly, this description can NOT be null
        if (null === $description) {
            $description = 'Thelia order ' . $order->getId();
        }

        $payPalOrderEvent = $this->generatePayPalOrder($order, null, $planifiedPayment);

        $merchantPreferences = $this->createMerchantPreferences($order);
        $chargeModel = $this->createChargeModel($order);

        $totalAmount = $order->getTotalAmount();
        $cycleAmount = round($totalAmount / $planifiedPayment->getCycle(), 2);

        $paymentDefinition = $this->createPaymentDefinition(
            $order,
            'payment definition for order ' . $order->getId(),
            [$chargeModel],
            $cycleAmount,
            self::PAYMENT_TYPE_REGULAR,
            $planifiedPayment->getFrequency(),
            $planifiedPayment->getFrequencyInterval(),
            $planifiedPayment->getCycle()
        );

        $plan = $this->generateBillingPlan($order, 'plan for order ' . $order->getId(), $merchantPreferences, [$paymentDefinition]);
        $plan = $this->createBillingPlan($order, $plan);
        $plan = $this->activateBillingPlan($order, $plan);

        $newPlan = new Plan();
        $newPlan->setId($plan->getId());

        // There is no Billing agreement possible with credit card
        $agreement = $this->createBillingAgreementWithPayPal($order, $newPlan, 'agreement ' . $order->getId(), $description);

        //We must update concerned order_product price... order discount... order postage... PayPal will create one invoice each cycle
        $this->updateTheliaOrderForCycle($order, $planifiedPayment->getCycle(), $cycleAmount);

        $this->updatePayPalOrder($payPalOrderEvent->getPayPalOrder(), $agreement->getState(), null, $agreement->getId());

        return $agreement;
    }

    public function updateTheliaOrderForCycle(Order $order, $cycle, $cycleAmount)
    {
        //Be sure that there is no rounding price lost with this method
        $moneyLeft = $cycleAmount;

        $newPostage = round($order->getPostage() / $cycle, 2);
        $newPostageTax = round($order->getPostageTax() / $cycle, 2);
        $newDiscount = round($order->getDiscount() / $cycle, 2);

        $moneyLeft -= ($newPostage + $newPostageTax + $newDiscount);
        $orderProducts = OrderProductQuery::create()->filterByOrderId($order->getId())->find();

        /** @var \Thelia\Model\OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            $newPrice = round($orderProduct->getPrice() / $cycle, 2);
            $newPromoPrice = round($orderProduct->getPrice() / $cycle, 2);

            if ($orderProduct->getWasInPromo()) {
                $moneyLeft -= $newPromoPrice;
            } else {
                $moneyLeft -= $newPrice;
            }

            $orderProduct
                ->setPrice($newPrice)
                ->setPromoPrice($newPromoPrice)
                ->save()
            ;
            $taxes = OrderProductTaxQuery::create()->filterByOrderProductId($orderProduct->getId())->find();

            /** @var \Thelia\Model\OrderProductTax $tax */
            foreach ($taxes as $tax) {
                $newAmount = round($tax->getAmount() / $cycle, 2);
                $newPromoAmount = round($tax->getPromoAmount() / $cycle, 2);

                if ($orderProduct->getWasInPromo()) {
                    $moneyLeft -= $newPromoAmount;
                } else {
                    $moneyLeft -= $newAmount;
                }

                $tax
                    ->setAmount($newAmount)
                    ->setPromoAmount($newPromoAmount)
                    ->save()
                ;
            }
        }

        //Normally, $moneyLeft == 0 here. But in case of rouding price, adjust the rounding in the postage column
        $newPostage += $moneyLeft;

        $order
            ->setPostage($newPostage)
            ->setPostageTax($newPostageTax)
            ->setDiscount($newDiscount)
            ->save()
        ;

        return $order;
    }

    /**
     * @param $billingPlanId
     * @return Plan
     */
    public function getBillingPlan($billingPlanId)
    {
        $plan = Plan::get($billingPlanId, self::getApiContext());

        return $plan;
    }

    /**
     * @param Order $order
     * @param Plan $plan
     * @return Plan
     */
    public function activateBillingPlan(Order $order, Plan $plan)
    {
        $patch = new Patch();

        $value = new PayPalModel('{
	       "state":"ACTIVE"
	     }');

        $patch
            ->setOp('replace')
            ->setPath('/')
            ->setValue($value)
        ;

        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);

        $plan->update($patchRequest, self::getApiContext());
        $plan = $this->getBillingPlan($plan->getId());

        if (null === $payPalPlan = PaypalPlanQuery::create()
                ->filterByPaypalOrderId($order->getId())
                ->filterByPlanId($plan->getId())
                ->findOne()) {
            $payPalPlan = new PaypalPlan();
            $payPalPlan
                ->setPaypalOrderId($order->getId())
                ->setPlanId($plan->getId())
            ;
        }

        $payPalPlan->setState($plan->getState());
        $payPalPlanEvent = new PayPalPlanEvent($payPalPlan);
        $this->dispatcher->dispatch(PayPalEvents::PAYPAL_PLAN_UPDATE, $payPalPlanEvent);

        return $plan;
    }

    /**
     * @param $token
     * @param null $orderId
     * @return Agreement
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function activateBillingAgreementByToken($token, $orderId = null)
    {
        $agreement = new Agreement();

        try {
            $agreement->execute($token, self::getApiContext());

            return $this->getBillingAgreement($agreement->getId());
        }  catch (PayPalConnectionException $e) {
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $orderId
                ],
                Logger::CRITICAL
            );
            throw $e;
        } catch (\Exception $e) {
            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $orderId
                ],
                Logger::CRITICAL
            );
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param $name
     * @param $merchantPreferences
     * @param array $paymentDefinitions
     * @param string $description
     * @param string $type
     * @return Plan
     * @throws \Exception
     */
    public function generateBillingPlan(Order $order, $name, $merchantPreferences, $paymentDefinitions = [], $description = '', $type = self::PLAN_TYPE_FIXED)
    {
        if (!in_array($type, self::getAllowedPlanType())) {
            $message = Translator::getInstance()->trans(
                'Invalid type send to generate billing plan',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        if (!is_array($paymentDefinitions) || count($paymentDefinitions) <= 0) {
            $message = Translator::getInstance()->trans(
                'Invalid number of payment definition send to generate billing plan',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        $plan = new Plan();
        $plan
            ->setName(substr($name, 0, self::MAX_API_LENGHT))
            ->setDescription(substr($description, 0, self::MAX_API_LENGHT))
            ->setType($type)
            ->setPaymentDefinitions($paymentDefinitions)
            ->setMerchantPreferences($merchantPreferences)
        ;

        return $plan;
    }

    /**
     * @param Plan $plan
     * @return bool
     */
    public function deleteBillingPlan(Plan $plan)
    {
        $isDeleted = $plan->delete(self::getApiContext());

        return $isDeleted;
    }

    /**
     * @param int $pageSize
     * @return \PayPal\Api\PlanList
     */
    public function listBillingPlans($pageSize = 2)
    {
        $planList = Plan::all(['page_size' => $pageSize], self::getApiContext());

        return $planList;
    }

    /**
     * @param Order $order
     * @param Plan $plan
     * @return Plan
     * @throws PayPalConnectionException
     * @throws \Exception
     */
    public function createBillingPlan(Order $order, Plan $plan)
    {
        try {
            $plan = $plan->create(self::getApiContext());

            if (null === $payPalPlan = PaypalPlanQuery::create()
                    ->filterByPaypalOrderId($order->getId())
                    ->filterByPlanId($plan->getId())
                    ->findOne()) {
                $payPalPlan = new PaypalPlan();
                $payPalPlan
                    ->setPaypalOrderId($order->getId())
                    ->setPlanId($plan->getId())
                ;
            }

            $payPalPlan->setState($plan->getState());
            $payPalPlanEvent = new PayPalPlanEvent($payPalPlan);
            $this->dispatcher->dispatch(PayPalEvents::PAYPAL_PLAN_CREATE, $payPalPlanEvent);

            return $plan;
        }  catch (PayPalConnectionException $e) {
            $message = sprintf('url : %s. data : %s. message : %s', $e->getUrl(), $e->getData(), $e->getMessage());
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::CRITICAL
            );
            throw $e;
        } catch (\Exception $e) {
            PayPalLoggerService::log(
                $e->getMessage(),
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::CRITICAL
            );
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Plan $plan
     * @param $creditCardId
     * @param $name
     * @param $description
     * @return Agreement
     */
    public function createBillingAgreementWithCreditCard(Order $order, Plan $plan, $creditCardId, $name, $description)
    {
        $creditCardToken = new CreditCardToken();
        $creditCardToken->setCreditCardId($creditCardId);

        $fundingInstrument = new FundingInstrument();
        //$fundingInstrument->setCreditCardToken($creditCardToken);

        $card = new CreditCard();
        $card
            ->setType('visa')
            ->setNumber('4491759698858890')
            ->setExpireMonth('12')
            ->setExpireYear('2017')
            ->setCvv2('128')
        ;
        $fundingInstrument->setCreditCard($card);

        $payer = self::generatePayer(
            PayPal::PAYPAL_METHOD_CREDIT_CARD,
            [$fundingInstrument],
            self::generatePayerInfo(['email' => $order->getCustomer()->getEmail()])
        );

        $agreement = $this->generateAgreement($order, $plan, $payer, $name, $description);

        $agreement = $agreement->create(self::getApiContext());

        return $agreement;
    }

    /**
     * @param Order $order
     * @param Plan $plan
     * @param $name
     * @param $description
     * @return Agreement
     */
    public function createBillingAgreementWithPayPal(Order $order, Plan $plan, $name, $description)
    {
        $payer = self::generatePayer(PayPal::PAYPAL_METHOD_PAYPAL);

        $agreement = $this->generateAgreement($order, $plan, $payer, $name, $description);

        $agreement = $agreement->create(self::getApiContext());

        return $agreement;
    }

    /**
     * @param $agreementId
     * @return Agreement
     */
    public function getBillingAgreement($agreementId)
    {
        $agreement = Agreement::get($agreementId, self::getApiContext());

        return $agreement;
    }

    /**
     * @param $agreementId
     * @param array $params
     * @return \PayPal\Api\AgreementTransactions
     */
    public function getBillingAgreementTransactions($agreementId, $params = [])
    {
        if (is_array($params) || count($params) == 0) {
            $params = [
                'start_date' => date('Y-m-d', strtotime('-15 years')),
                'end_date' => date('Y-m-d', strtotime('+5 days'))
            ];
        }

        $agreementTransactions = Agreement::searchTransactions($agreementId, $params, self::getApiContext());

        return $agreementTransactions;
    }

    /**
     * @param Agreement $agreement
     * @param string $note
     * @return Agreement
     */
    public function suspendBillingAgreement(Agreement $agreement, $note = 'Suspending the agreement')
    {
        //Create an Agreement State Descriptor, explaining the reason to suspend.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote($note);

        $agreement->suspend($agreementStateDescriptor, self::getApiContext());

        $agreement = $this->getBillingAgreement($agreement->getId());

        return $agreement;
    }

    /**
     * @param Agreement $agreement
     * @param string $note
     * @return Agreement
     */
    public function reActivateBillingAgreement(Agreement $agreement, $note = 'Reactivating the agreement')
    {
        //Create an Agreement State Descriptor, explaining the reason to re activate.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote($note);

        $agreement->reActivate($agreementStateDescriptor, self::getApiContext());

        $agreement = $this->getBillingAgreement($agreement->getId());

        return $agreement;
    }

    /**
     * @param Order $order
     * @param $name
     * @param array $chargeModels
     * @param null $cycleAmount
     * @param string $type
     * @param string $frequency
     * @param int $frequencyInterval
     * @param int $cycles
     * @return PaymentDefinition
     * @throws \Exception
     */
    public function createPaymentDefinition(Order $order, $name, $chargeModels = [], $cycleAmount = null, $type = self::PAYMENT_TYPE_REGULAR, $frequency = self::PAYMENT_FREQUENCY_DAY, $frequencyInterval = 1, $cycles = 1)
    {
        if (!in_array($type, self::getAllowedPaymentType())) {
            $message = Translator::getInstance()->trans(
                'Invalid payment type send to create payment definition',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        if (!in_array($frequency, self::getAllowedPaymentFrequency())) {
            $message = Translator::getInstance()->trans(
                'Invalid payment frequency send to create payment definition',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        if (!is_array($chargeModels) || count($chargeModels) <= 0) {
            $message = Translator::getInstance()->trans(
                'Invalid number of charge models send to create payment definition',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        $paymentDefinition = new PaymentDefinition();

        if (null === $cycleAmount) {
            $totalAmount = $order->getTotalAmount();
            $cycleAmount = round($totalAmount / $cycles, 2);
        }

        $paymentDefinition
            ->setName(substr($name, 0, self::MAX_API_LENGHT))
            ->setType($type)
            ->setFrequency($frequency)
            ->setFrequencyInterval($frequencyInterval)
            ->setCycles($cycles)
            ->setAmount(new Currency(['value' => $cycleAmount, 'currency' => self::getOrderCurrencyCode($order)]))
            ->setChargeModels($chargeModels)
        ;

        return $paymentDefinition;
    }

    /**
     * @param Order $order
     * @param int $chargeAmount
     * @param string $type
     * @return ChargeModel
     * @throws \Exception
     */
    public function createChargeModel(Order $order, $chargeAmount = 0, $type = self::CHARGE_TYPE_SHIPPING)
    {
        if (!in_array($type, self::getAllowedChargeType())) {
            $message = Translator::getInstance()->trans(
                'Invalid charge type send to create charge model',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        $chargeModel = new ChargeModel();
        $chargeModel
            ->setType($type)
            ->setAmount(new Currency(['value' => $chargeAmount, 'currency' => self::getOrderCurrencyCode($order)]))
        ;

        return $chargeModel;
    }

    /**
     * @param Order $order
     * @param bool $autoBillAmount
     * @param string $failAction
     * @param int $maxFailAttempts
     * @param int $feeAmount
     * @return MerchantPreferences
     * @throws \Exception
     */
    public function createMerchantPreferences(Order $order, $autoBillAmount = false, $failAction = self::FAIL_AMOUNT_ACTION_CONTINUE, $maxFailAttempts = 0, $feeAmount = 0)
    {
        if (!in_array($failAction, self::getAllowedFailedAction())) {
            $message = Translator::getInstance()->trans(
                'Invalid fail action send to create merchant preference',
                [],
                PayPal::DOMAIN_NAME
            );
            PayPalLoggerService::log(
                $message,
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $order->getId()
                ],
                Logger::ERROR
            );
            throw new \Exception($message);
        }

        $merchantPreferences = new MerchantPreferences();

        $urlOk = URL::getInstance()->absoluteUrl(
            $this->router->generate(
                "paypal.agreement.ok",
                [
                    'orderId' => $order->getId()
                ],
                Router::ABSOLUTE_URL
            )
        );
        $urlKo = URL::getInstance()->absoluteUrl(
            $this->router->generate(
                "paypal.agreement.ko",
                [
                    'orderId' => $order->getId()
                ],
                Router::ABSOLUTE_URL
            )
        );

        if ($autoBillAmount) {
            $autoBillAmountStr = 'YES';
        } else {
            $autoBillAmountStr = 'NO';
        }

        $merchantPreferences
            ->setReturnUrl($urlOk)
            ->setCancelUrl($urlKo)
            ->setAutoBillAmount($autoBillAmountStr)
            ->setInitialFailAmountAction($failAction)
            ->setMaxFailAttempts($maxFailAttempts)
            ->setSetupFee(new Currency(['value' => $feeAmount, 'currency' => self::getOrderCurrencyCode($order)]))
        ;

        return $merchantPreferences;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function duplicateOrder(Order $order)
    {
        $today = new \Datetime;
        $newOrder = new Order();
        $newOrder
            ->setCustomerId($order->getCustomerId())
            ->setInvoiceOrderAddressId($order->getInvoiceOrderAddressId())
            ->setDeliveryOrderAddressId($order->getDeliveryOrderAddressId())
            ->setInvoiceDate($today->format('Y-m-d H:i:s'))
            ->setCurrencyId($order->getCurrencyId())
            ->setCurrencyRate($order->getCurrencyRate())
            ->setDeliveryRef($order->getDeliveryRef())
            ->setInvoiceRef($order->getInvoiceRef())
            ->setDiscount($order->getDiscount())
            ->setPostage($order->getPostage())
            ->setPostageTax($order->getPostageTax())
            ->setPostageTaxRuleTitle($order->getPostageTaxRuleTitle())
            ->setPaymentModuleId($order->getPaymentModuleId())
            ->setDeliveryModuleId($order->getDeliveryModuleId())
            ->setStatusId($order->getStatusId())
            ->setLangId($order->getLangId())
            ->setCartId($order->getCartId())
            ->save()
        ;

        $orderProducts = OrderProductQuery::create()->filterByOrderId($order->getId())->find();

        /** @var \Thelia\Model\OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            $newOrderProduct = new OrderProduct();
            $newOrderProduct
                ->setOrderId($newOrder->getId())
                ->setProductRef($orderProduct->getProductRef())
                ->setProductSaleElementsRef($orderProduct->getProductSaleElementsRef())
                ->setProductSaleElementsId($orderProduct->getProductSaleElementsId())
                ->setTitle($orderProduct->getTitle())
                ->setChapo($orderProduct->getChapo())
                ->setDescription($orderProduct->getDescription())
                ->setPostscriptum($orderProduct->getPostscriptum())
                ->setQuantity($orderProduct->getQuantity())
                ->setPrice($orderProduct->getPrice())
                ->setPromoPrice($orderProduct->getPromoPrice())
                ->setWasNew($orderProduct->getWasNew())
                ->setWasInPromo($orderProduct->getWasInPromo())
                ->setWeight($orderProduct->getWeight())
                ->setEanCode($orderProduct->getEanCode())
                ->setTaxRuleTitle($orderProduct->getTaxRuleTitle())
                ->setTaxRuleDescription($orderProduct->getTaxRuleDescription())
                ->setParent($orderProduct->getParent())
                ->setVirtual($orderProduct->getVirtual())
                ->setVirtualDocument($orderProduct->getVirtualDocument())
                ->save()
            ;

            $orderProductTaxes = OrderProductTaxQuery::create()->filterByOrderProductId($orderProduct->getId())->find();

            /** @var \Thelia\Model\OrderProductTax $orderProductTax */
            foreach ($orderProductTaxes as $orderProductTax) {

                $newOrderProductTax = new OrderProductTax();
                $newOrderProductTax
                    ->setOrderProductId($newOrderProduct->getId())
                    ->setTitle($orderProductTax->getTitle())
                    ->setDescription($orderProductTax->getDescription())
                    ->setAmount($orderProductTax->getAmount())
                    ->setPromoAmount($orderProductTax->getPromoAmount())
                    ->save()
                ;
            }
        }

        if (null !== $payPalOrder = PaypalOrderQuery::create()->findOneById($order->getId())) {
            $newPayPalOrder = new PaypalOrder();
            $newPayPalOrder
                ->setId($newOrder->getId())
                ->setPaymentId($payPalOrder->getPaymentId())
                ->setAgreementId($payPalOrder->getAgreementId())
                ->setCreditCardId($payPalOrder->getCreditCardId())
                ->setState($payPalOrder->getState())
                ->setAmount($payPalOrder->getAmount())
                ->setDescription($payPalOrder->getDescription())
                ->setPayerId($payPalOrder->getPayerId())
                ->setToken($payPalOrder->getToken())
            ;
            $newPayPalOrderEvent = new PayPalOrderEvent($newPayPalOrder);
            $this->dispatcher->dispatch(PayPalEvents::PAYPAL_ORDER_CREATE, $newPayPalOrderEvent);

            $payPalPlans = PaypalPlanQuery::create()->filterByPaypalOrderId($payPalOrder->getId());

            /** @var \PayPal\Model\PaypalPlan $payPalPlan */
            foreach ($payPalPlans as $payPalPlan) {

                $newPayPalPlan = new PaypalPlan();
                $newPayPalPlan
                    ->setPaypalOrderId($newPayPalOrderEvent->getPayPalOrder()->getId())
                    ->setPlanId($payPalPlan->getPlanId())
                    ->setState($payPalPlan->getState())
                ;

                $newPayPalPlanEvent = new PayPalPlanEvent($newPayPalPlan);
                $this->dispatcher->dispatch(PayPalEvents::PAYPAL_PLAN_CREATE, $newPayPalPlanEvent);
            }
        }

        return $newOrder;
    }

    /**
     * @param Order $order
     * @param Plan $plan
     * @param Payer $payer
     * @param $name
     * @param string $description
     * @return Agreement
     * @throws \Exception
     */
    public function generateAgreement(Order $order, Plan $plan, Payer $payer, $name, $description = '')
    {
        $agreement = new Agreement();
        $agreement
            ->setName($name)
            ->setDescription($description)
            ->setStartDate((new \Datetime)->format('Y-m-d\TG:i:s\Z'))
            ->setPlan($plan)
        ;

        //Add Payer to Agreement
        $agreement
            ->setPayer($payer)
            ->setShippingAddress(self::generateShippingAddress($order))
        ;

        return $agreement;
    }

    /**
     * @param Order $order
     * @return string
     */
    public static function getOrderCurrencyCode(Order $order)
    {
        if (null === $currency = CurrencyQuery::create()->findOneById($order->getCurrencyId())) {
            $currency = \Thelia\Model\Currency::getDefaultCurrency();
        }

        return $currency->getCode();
    }

    /**
     * @return array
     */
    public static function getAllowedPlanType()
    {
        return [
            self::PLAN_TYPE_FIXED,
            self::PLAN_TYPE_INFINITE
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedPaymentType()
    {
        return [
            self::PAYMENT_TYPE_REGULAR,
            self::PAYMENT_TYPE_TRIAL
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedChargeType()
    {
        return [
            self::CHARGE_TYPE_SHIPPING,
            self::CHARGE_TYPE_TAX
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedPaymentFrequency()
    {
        return [
            self::PAYMENT_FREQUENCY_DAY => self::PAYMENT_FREQUENCY_DAY,
            self::PAYMENT_FREQUENCY_WEEK => self::PAYMENT_FREQUENCY_WEEK,
            self::PAYMENT_FREQUENCY_MONTH => self::PAYMENT_FREQUENCY_MONTH,
            self::PAYMENT_FREQUENCY_YEAR => self::PAYMENT_FREQUENCY_YEAR
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedFailedAction()
    {
        return [
            self::FAIL_AMOUNT_ACTION_CANCEL,
            self::FAIL_AMOUNT_ACTION_CONTINUE
        ];
    }
}
