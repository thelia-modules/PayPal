<?php
/**
 * Created by PhpStorm.
 * User: guigit
 * Date: 30/01/2017
 * Time: 14:08
 */

namespace PayPal\Loop;


use PayPal\Model\PaypalLog;
use PayPal\Model\PaypalLogQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class PayPalPlanifiedPaymentLoop
 * @package PayPal\Loop
 *
 * @method int getOrderId()
 * @method int getCustomerId()
 * @method string getChannel()
 * @method string getLevel()
 * @method string[] getOrder()
 */
class PayPalLogLoop extends BaseLoop implements PropelSearchLoopInterface
{
    // Allow to use substitution CREATED_AT, UPDATED_AT in loop
    protected $timestampable = true;

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /**
         * @var PaypalLog $model
         */
        foreach ($loopResult->getResultDataCollection() as $model) {

            $row = new LoopResultRow($model);

            $row->set('log', $model);

            $this->addOutputFields($row, $model);

            $loopResult->addRow($row);
        }

        return $loopResult;
    }

    /**
     * @return PaypalLogQuery
     */
    public function buildModelCriteria()
    {
        $query = new PaypalLogQuery();

        if (null != $orderId = $this->getOrderId()) {
            $query->filterByOrderId($orderId);
        }

        if (null != $customerId = $this->getCustomerId()) {
            $query->filterByCustomerId($customerId);
        }

        if (null != $channel = $this->getChannel()) {
            $query->filterByChannel($channel);
        }

        if (null != $level = $this->getLevel()) {
            $query->filterByLevel($level);
        }

        $this->buildModelCriteriaOrder($query);
        $query->groupById();

        return $query;
    }

    /**
     * @param PaypalLogQuery $query
     */
    protected function buildModelCriteriaOrder(PaypalLogQuery $query)
    {
        foreach ($this->getOrder() as $order) {
            switch ($order) {
                case 'id':
                    $query->orderById();
                    break;
                case 'id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                case 'order-id':
                    $query->orderById();
                    break;
                case 'order-id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                case 'customer-id':
                    $query->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case 'customer-id-reverse':
                    $query->addDescendingOrderByColumn('i18n_TITLE');
                    break;
                case 'date':
                    $query->orderByCreatedAt();
                    break;
                case 'date-reverse':
                    $query->orderByCreatedAt(Criteria::DESC);
                    break;
                default:
                    $query->orderById();
                    break;
            }
        }
    }

    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('order_id'),
            Argument::createIntTypeArgument('customer_id'),
            Argument::createAnyTypeArgument('channel'),
            Argument::createIntTypeArgument('level'),
            Argument::createEnumListTypeArgument(
                'order',
                [
                    'id',
                    'id-reverse',
                    'order-id',
                    'order-id-reverse',
                    'customer-id',
                    'customer-id-reverse',
                    'date',
                    'date-reverse',
                ],
                'id'
            )
        );
    }
}
