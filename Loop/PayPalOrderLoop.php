<?php
/**
 * Created by PhpStorm.
 * User: guigit
 * Date: 20/01/2017
 * Time: 11:50
 */

namespace PayPal\Loop;


use PayPal\Model\PaypalOrder;
use PayPal\Model\PaypalOrderQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class PayPalOrderLoop
 * @package PayPal\Loop
 *
 * @method int getId()
 * @method string[] getOrder()
 */
class PayPalOrderLoop extends BaseLoop implements PropelSearchLoopInterface
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
         * @var PaypalOrder $model
         */
        foreach ($loopResult->getResultDataCollection() as $model) {
            $row = new LoopResultRow($model);

            $row->set('paypal_order', $model);

            $this->addOutputFields($row, $model);

            $loopResult->addRow($row);
        }

        return $loopResult;
    }

    /**
     * @return PaypalOrderQuery
     */
    public function buildModelCriteria()
    {
        $query = new PaypalOrderQuery();

        if (null != $id = $this->getId()) {
            $query->filterById($id);
        }

        $this->buildModelCriteriaOrder($query);

        return $query;
    }

    /**
     * @param PaypalOrderQuery $query
     */
    protected function buildModelCriteriaOrder(PaypalOrderQuery $query)
    {
        foreach ($this->getOrder() as $order) {
            switch ($order) {
                case 'id':
                    $query->orderById();
                    break;
                case 'id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                default:
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
            Argument::createIntListTypeArgument('id'),
            Argument::createEnumListTypeArgument(
                'order',
                [
                    'id',
                    'id-reverse'
                ],
                'id'
            )
        );
    }
}
