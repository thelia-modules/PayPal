<?php

namespace PayPal\Loop;

use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class PayPalPlanifiedPaymentLoop
 * @package PayPal\Loop
 *
 * @method int getId()
 * @method string[] getOrder()
 */
class PayPalPlanifiedPaymentLoop extends BaseI18nLoop implements PropelSearchLoopInterface
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
        /** @var \Thelia\Model\Lang $lang */
        $lang = $this->getCurrentRequest()->getSession()->get('thelia.current.lang');

        /**
         * @var PaypalPlanifiedPayment $model
         */
        foreach ($loopResult->getResultDataCollection() as $model) {
            $model->getTranslation($lang->getLocale());
            $row = new LoopResultRow($model);

            $row->set('planifiedPayment', $model);

            $this->addOutputFields($row, $model);

            $loopResult->addRow($row);
        }

        return $loopResult;
    }

    /**
     * @return PaypalPlanifiedPaymentQuery
     */
    public function buildModelCriteria()
    {
        $query = new PaypalPlanifiedPaymentQuery();

        if (null != $id = $this->getId()) {
            $query->filterById($id);
        }

        /* manage translations */
        $this->configureI18nProcessing(
            $query,
            array(
                'TITLE',
                'DESCRIPTION'
            )
        );

        $this->buildModelCriteriaOrder($query);
        $query->groupById();

        return $query;
    }

    /**
     * @param PaypalPlanifiedPaymentQuery $query
     */
    protected function buildModelCriteriaOrder(PaypalPlanifiedPaymentQuery $query)
    {
        foreach ($this->getOrder() as $order) {
            switch ($order) {
                case 'id':
                    $query->orderById();
                    break;
                case 'id-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                case 'position':
                    $query->orderById();
                    break;
                case 'position-reverse':
                    $query->orderById(Criteria::DESC);
                    break;
                case 'title':
                    $query->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case 'title-reverse':
                    $query->addDescendingOrderByColumn('i18n_TITLE');
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
            Argument::createIntListTypeArgument('id'),
            Argument::createEnumListTypeArgument(
                'order',
                [
                    'id',
                    'id-reverse',
                    'position',
                    'position-reverse',
                    'title',
                    'title-reverse',
                ],
                'id'
            )
        );
    }
}
