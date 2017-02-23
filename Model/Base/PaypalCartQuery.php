<?php

namespace PayPal\Model\Base;

use \Exception;
use \PDO;
use PayPal\Model\PaypalCart as ChildPaypalCart;
use PayPal\Model\PaypalCartQuery as ChildPaypalCartQuery;
use PayPal\Model\Map\PaypalCartTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Cart;

/**
 * Base class that represents a query for the 'paypal_cart' table.
 *
 *
 *
 * @method     ChildPaypalCartQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPaypalCartQuery orderByCreditCardId($order = Criteria::ASC) Order by the credit_card_id column
 * @method     ChildPaypalCartQuery orderByPlanifiedPaymentId($order = Criteria::ASC) Order by the planified_payment_id column
 * @method     ChildPaypalCartQuery orderByExpressPaymentId($order = Criteria::ASC) Order by the express_payment_id column
 * @method     ChildPaypalCartQuery orderByExpressPayerId($order = Criteria::ASC) Order by the express_payer_id column
 * @method     ChildPaypalCartQuery orderByExpressToken($order = Criteria::ASC) Order by the express_token column
 * @method     ChildPaypalCartQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildPaypalCartQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildPaypalCartQuery groupById() Group by the id column
 * @method     ChildPaypalCartQuery groupByCreditCardId() Group by the credit_card_id column
 * @method     ChildPaypalCartQuery groupByPlanifiedPaymentId() Group by the planified_payment_id column
 * @method     ChildPaypalCartQuery groupByExpressPaymentId() Group by the express_payment_id column
 * @method     ChildPaypalCartQuery groupByExpressPayerId() Group by the express_payer_id column
 * @method     ChildPaypalCartQuery groupByExpressToken() Group by the express_token column
 * @method     ChildPaypalCartQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildPaypalCartQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildPaypalCartQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPaypalCartQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPaypalCartQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPaypalCartQuery leftJoinCart($relationAlias = null) Adds a LEFT JOIN clause to the query using the Cart relation
 * @method     ChildPaypalCartQuery rightJoinCart($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Cart relation
 * @method     ChildPaypalCartQuery innerJoinCart($relationAlias = null) Adds a INNER JOIN clause to the query using the Cart relation
 *
 * @method     ChildPaypalCartQuery leftJoinPaypalPlanifiedPayment($relationAlias = null) Adds a LEFT JOIN clause to the query using the PaypalPlanifiedPayment relation
 * @method     ChildPaypalCartQuery rightJoinPaypalPlanifiedPayment($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PaypalPlanifiedPayment relation
 * @method     ChildPaypalCartQuery innerJoinPaypalPlanifiedPayment($relationAlias = null) Adds a INNER JOIN clause to the query using the PaypalPlanifiedPayment relation
 *
 * @method     ChildPaypalCart findOne(ConnectionInterface $con = null) Return the first ChildPaypalCart matching the query
 * @method     ChildPaypalCart findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPaypalCart matching the query, or a new ChildPaypalCart object populated from the query conditions when no match is found
 *
 * @method     ChildPaypalCart findOneById(int $id) Return the first ChildPaypalCart filtered by the id column
 * @method     ChildPaypalCart findOneByCreditCardId(string $credit_card_id) Return the first ChildPaypalCart filtered by the credit_card_id column
 * @method     ChildPaypalCart findOneByPlanifiedPaymentId(int $planified_payment_id) Return the first ChildPaypalCart filtered by the planified_payment_id column
 * @method     ChildPaypalCart findOneByExpressPaymentId(string $express_payment_id) Return the first ChildPaypalCart filtered by the express_payment_id column
 * @method     ChildPaypalCart findOneByExpressPayerId(string $express_payer_id) Return the first ChildPaypalCart filtered by the express_payer_id column
 * @method     ChildPaypalCart findOneByExpressToken(string $express_token) Return the first ChildPaypalCart filtered by the express_token column
 * @method     ChildPaypalCart findOneByCreatedAt(string $created_at) Return the first ChildPaypalCart filtered by the created_at column
 * @method     ChildPaypalCart findOneByUpdatedAt(string $updated_at) Return the first ChildPaypalCart filtered by the updated_at column
 *
 * @method     array findById(int $id) Return ChildPaypalCart objects filtered by the id column
 * @method     array findByCreditCardId(string $credit_card_id) Return ChildPaypalCart objects filtered by the credit_card_id column
 * @method     array findByPlanifiedPaymentId(int $planified_payment_id) Return ChildPaypalCart objects filtered by the planified_payment_id column
 * @method     array findByExpressPaymentId(string $express_payment_id) Return ChildPaypalCart objects filtered by the express_payment_id column
 * @method     array findByExpressPayerId(string $express_payer_id) Return ChildPaypalCart objects filtered by the express_payer_id column
 * @method     array findByExpressToken(string $express_token) Return ChildPaypalCart objects filtered by the express_token column
 * @method     array findByCreatedAt(string $created_at) Return ChildPaypalCart objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildPaypalCart objects filtered by the updated_at column
 *
 */
abstract class PaypalCartQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \PayPal\Model\Base\PaypalCartQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\PayPal\\Model\\PaypalCart', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPaypalCartQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPaypalCartQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \PayPal\Model\PaypalCartQuery) {
            return $criteria;
        }
        $query = new \PayPal\Model\PaypalCartQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildPaypalCart|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaypalCartTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalCartTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildPaypalCart A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, CREDIT_CARD_ID, PLANIFIED_PAYMENT_ID, EXPRESS_PAYMENT_ID, EXPRESS_PAYER_ID, EXPRESS_TOKEN, CREATED_AT, UPDATED_AT FROM paypal_cart WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildPaypalCart();
            $obj->hydrate($row);
            PaypalCartTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildPaypalCart|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PaypalCartTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PaypalCartTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @see       filterByCart()
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PaypalCartTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PaypalCartTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the credit_card_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCreditCardId('fooValue');   // WHERE credit_card_id = 'fooValue'
     * $query->filterByCreditCardId('%fooValue%'); // WHERE credit_card_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $creditCardId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByCreditCardId($creditCardId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($creditCardId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $creditCardId)) {
                $creditCardId = str_replace('*', '%', $creditCardId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::CREDIT_CARD_ID, $creditCardId, $comparison);
    }

    /**
     * Filter the query on the planified_payment_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedPaymentId(1234); // WHERE planified_payment_id = 1234
     * $query->filterByPlanifiedPaymentId(array(12, 34)); // WHERE planified_payment_id IN (12, 34)
     * $query->filterByPlanifiedPaymentId(array('min' => 12)); // WHERE planified_payment_id > 12
     * </code>
     *
     * @see       filterByPaypalPlanifiedPayment()
     *
     * @param     mixed $planifiedPaymentId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByPlanifiedPaymentId($planifiedPaymentId = null, $comparison = null)
    {
        if (is_array($planifiedPaymentId)) {
            $useMinMax = false;
            if (isset($planifiedPaymentId['min'])) {
                $this->addUsingAlias(PaypalCartTableMap::PLANIFIED_PAYMENT_ID, $planifiedPaymentId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedPaymentId['max'])) {
                $this->addUsingAlias(PaypalCartTableMap::PLANIFIED_PAYMENT_ID, $planifiedPaymentId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::PLANIFIED_PAYMENT_ID, $planifiedPaymentId, $comparison);
    }

    /**
     * Filter the query on the express_payment_id column
     *
     * Example usage:
     * <code>
     * $query->filterByExpressPaymentId('fooValue');   // WHERE express_payment_id = 'fooValue'
     * $query->filterByExpressPaymentId('%fooValue%'); // WHERE express_payment_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $expressPaymentId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByExpressPaymentId($expressPaymentId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($expressPaymentId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $expressPaymentId)) {
                $expressPaymentId = str_replace('*', '%', $expressPaymentId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::EXPRESS_PAYMENT_ID, $expressPaymentId, $comparison);
    }

    /**
     * Filter the query on the express_payer_id column
     *
     * Example usage:
     * <code>
     * $query->filterByExpressPayerId('fooValue');   // WHERE express_payer_id = 'fooValue'
     * $query->filterByExpressPayerId('%fooValue%'); // WHERE express_payer_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $expressPayerId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByExpressPayerId($expressPayerId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($expressPayerId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $expressPayerId)) {
                $expressPayerId = str_replace('*', '%', $expressPayerId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::EXPRESS_PAYER_ID, $expressPayerId, $comparison);
    }

    /**
     * Filter the query on the express_token column
     *
     * Example usage:
     * <code>
     * $query->filterByExpressToken('fooValue');   // WHERE express_token = 'fooValue'
     * $query->filterByExpressToken('%fooValue%'); // WHERE express_token LIKE '%fooValue%'
     * </code>
     *
     * @param     string $expressToken The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByExpressToken($expressToken = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($expressToken)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $expressToken)) {
                $expressToken = str_replace('*', '%', $expressToken);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::EXPRESS_TOKEN, $expressToken, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(PaypalCartTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(PaypalCartTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(PaypalCartTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(PaypalCartTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCartTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\Cart object
     *
     * @param \Thelia\Model\Cart|ObjectCollection $cart The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByCart($cart, $comparison = null)
    {
        if ($cart instanceof \Thelia\Model\Cart) {
            return $this
                ->addUsingAlias(PaypalCartTableMap::ID, $cart->getId(), $comparison);
        } elseif ($cart instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalCartTableMap::ID, $cart->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCart() only accepts arguments of type \Thelia\Model\Cart or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Cart relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function joinCart($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Cart');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Cart');
        }

        return $this;
    }

    /**
     * Use the Cart relation Cart object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\CartQuery A secondary query class using the current class as primary query
     */
    public function useCartQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCart($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Cart', '\Thelia\Model\CartQuery');
    }

    /**
     * Filter the query by a related \PayPal\Model\PaypalPlanifiedPayment object
     *
     * @param \PayPal\Model\PaypalPlanifiedPayment|ObjectCollection $paypalPlanifiedPayment The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function filterByPaypalPlanifiedPayment($paypalPlanifiedPayment, $comparison = null)
    {
        if ($paypalPlanifiedPayment instanceof \PayPal\Model\PaypalPlanifiedPayment) {
            return $this
                ->addUsingAlias(PaypalCartTableMap::PLANIFIED_PAYMENT_ID, $paypalPlanifiedPayment->getId(), $comparison);
        } elseif ($paypalPlanifiedPayment instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalCartTableMap::PLANIFIED_PAYMENT_ID, $paypalPlanifiedPayment->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByPaypalPlanifiedPayment() only accepts arguments of type \PayPal\Model\PaypalPlanifiedPayment or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PaypalPlanifiedPayment relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function joinPaypalPlanifiedPayment($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PaypalPlanifiedPayment');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'PaypalPlanifiedPayment');
        }

        return $this;
    }

    /**
     * Use the PaypalPlanifiedPayment relation PaypalPlanifiedPayment object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \PayPal\Model\PaypalPlanifiedPaymentQuery A secondary query class using the current class as primary query
     */
    public function usePaypalPlanifiedPaymentQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPaypalPlanifiedPayment($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PaypalPlanifiedPayment', '\PayPal\Model\PaypalPlanifiedPaymentQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPaypalCart $paypalCart Object to remove from the list of results
     *
     * @return ChildPaypalCartQuery The current query, for fluid interface
     */
    public function prune($paypalCart = null)
    {
        if ($paypalCart) {
            $this->addUsingAlias(PaypalCartTableMap::ID, $paypalCart->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the paypal_cart table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCartTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PaypalCartTableMap::clearInstancePool();
            PaypalCartTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildPaypalCart or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildPaypalCart object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCartTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PaypalCartTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        PaypalCartTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PaypalCartTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalCartTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalCartTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalCartTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalCartTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalCartTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildPaypalCartQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalCartTableMap::CREATED_AT);
    }

} // PaypalCartQuery
