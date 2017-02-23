<?php

namespace PayPal\Loop;

use PayPal\Model\PaypalOrderVersion;
use PayPal\Model\PaypalOrderVersionQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class PayPalOrderVersionLoop
 * @package PayPal\Loop
 *
 * @method int getId()
 * @method string[] getOrder()
 */
class PayPalOrderVersionLoop extends BaseLoop implements PropelSearchLoopInterface
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
         * @var PayPalOrderVersion $model
         */
        foreach ($loopResult->getResultDataCollection() as $model) {
            $row = new LoopResultRow($model);

            $row->set('paypal_order_version', $model);

            $this->addOutputFields($row, $model);

            $loopResult->addRow($row);
        }

        return $loopResult;
    }

    /**
     * @return PaypalOrderVersionQuery
     */
    public function buildModelCriteria()
    {
        $query = new PaypalOrderVersionQuery();

        if (null != $id = $this->getId()) {
            $query->filterById($id);
        }

        $this->buildModelCriteriaOrder($query);

        return $query;
    }

    /**
     * @param PaypalOrderVersionQuery $query
     */
    protected function buildModelCriteriaOrder(PaypalOrderVersionQuery $query)
    {
        foreach ($this->getOrder() as $order) {
            switch ($order) {
                case 'id':
                    $query->orderById();
                    break;
                case 'id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                case 'version':
                    $query->orderByVersion();
                    break;
                case 'version-reverse':
                    $query->orderByVersion(Criteria::DESC);
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
                    'id-reverse',
                    'version',
                    'version-reverse'
                ],
                'version'
            )
        );
    }
}
