<?php

namespace PayPal\Model\Base;

use \Exception;
use \PDO;
use PayPal\Model\PaypalPlan as ChildPaypalPlan;
use PayPal\Model\PaypalPlanQuery as ChildPaypalPlanQuery;
use PayPal\Model\Map\PaypalPlanTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'paypal_plan' table.
 *
 *
 *
 * @method     ChildPaypalPlanQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPaypalPlanQuery orderByPaypalOrderId($order = Criteria::ASC) Order by the paypal_order_id column
 * @method     ChildPaypalPlanQuery orderByPlanId($order = Criteria::ASC) Order by the plan_id column
 * @method     ChildPaypalPlanQuery orderByState($order = Criteria::ASC) Order by the state column
 * @method     ChildPaypalPlanQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildPaypalPlanQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildPaypalPlanQuery groupById() Group by the id column
 * @method     ChildPaypalPlanQuery groupByPaypalOrderId() Group by the paypal_order_id column
 * @method     ChildPaypalPlanQuery groupByPlanId() Group by the plan_id column
 * @method     ChildPaypalPlanQuery groupByState() Group by the state column
 * @method     ChildPaypalPlanQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildPaypalPlanQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildPaypalPlanQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPaypalPlanQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPaypalPlanQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPaypalPlanQuery leftJoinPaypalOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the PaypalOrder relation
 * @method     ChildPaypalPlanQuery rightJoinPaypalOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PaypalOrder relation
 * @method     ChildPaypalPlanQuery innerJoinPaypalOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the PaypalOrder relation
 *
 * @method     ChildPaypalPlan findOne(ConnectionInterface $con = null) Return the first ChildPaypalPlan matching the query
 * @method     ChildPaypalPlan findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPaypalPlan matching the query, or a new ChildPaypalPlan object populated from the query conditions when no match is found
 *
 * @method     ChildPaypalPlan findOneById(int $id) Return the first ChildPaypalPlan filtered by the id column
 * @method     ChildPaypalPlan findOneByPaypalOrderId(int $paypal_order_id) Return the first ChildPaypalPlan filtered by the paypal_order_id column
 * @method     ChildPaypalPlan findOneByPlanId(string $plan_id) Return the first ChildPaypalPlan filtered by the plan_id column
 * @method     ChildPaypalPlan findOneByState(string $state) Return the first ChildPaypalPlan filtered by the state column
 * @method     ChildPaypalPlan findOneByCreatedAt(string $created_at) Return the first ChildPaypalPlan filtered by the created_at column
 * @method     ChildPaypalPlan findOneByUpdatedAt(string $updated_at) Return the first ChildPaypalPlan filtered by the updated_at column
 *
 * @method     array findById(int $id) Return ChildPaypalPlan objects filtered by the id column
 * @method     array findByPaypalOrderId(int $paypal_order_id) Return ChildPaypalPlan objects filtered by the paypal_order_id column
 * @method     array findByPlanId(string $plan_id) Return ChildPaypalPlan objects filtered by the plan_id column
 * @method     array findByState(string $state) Return ChildPaypalPlan objects filtered by the state column
 * @method     array findByCreatedAt(string $created_at) Return ChildPaypalPlan objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildPaypalPlan objects filtered by the updated_at column
 *
 */
abstract class PaypalPlanQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \PayPal\Model\Base\PaypalPlanQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\PayPal\\Model\\PaypalPlan', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPaypalPlanQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPaypalPlanQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \PayPal\Model\PaypalPlanQuery) {
            return $criteria;
        }
        $query = new \PayPal\Model\PaypalPlanQuery();
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
     * @return ChildPaypalPlan|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaypalPlanTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalPlanTableMap::DATABASE_NAME);
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
     * @return   ChildPaypalPlan A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PAYPAL_ORDER_ID, PLAN_ID, STATE, CREATED_AT, UPDATED_AT FROM paypal_plan WHERE ID = :p0';
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
            $obj = new ChildPaypalPlan();
            $obj->hydrate($row);
            PaypalPlanTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildPaypalPlan|array|mixed the result, formatted by the current formatter
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
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PaypalPlanTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PaypalPlanTableMap::ID, $keys, Criteria::IN);
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
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PaypalPlanTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PaypalPlanTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the paypal_order_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPaypalOrderId(1234); // WHERE paypal_order_id = 1234
     * $query->filterByPaypalOrderId(array(12, 34)); // WHERE paypal_order_id IN (12, 34)
     * $query->filterByPaypalOrderId(array('min' => 12)); // WHERE paypal_order_id > 12
     * </code>
     *
     * @see       filterByPaypalOrder()
     *
     * @param     mixed $paypalOrderId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByPaypalOrderId($paypalOrderId = null, $comparison = null)
    {
        if (is_array($paypalOrderId)) {
            $useMinMax = false;
            if (isset($paypalOrderId['min'])) {
                $this->addUsingAlias(PaypalPlanTableMap::PAYPAL_ORDER_ID, $paypalOrderId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($paypalOrderId['max'])) {
                $this->addUsingAlias(PaypalPlanTableMap::PAYPAL_ORDER_ID, $paypalOrderId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::PAYPAL_ORDER_ID, $paypalOrderId, $comparison);
    }

    /**
     * Filter the query on the plan_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanId('fooValue');   // WHERE plan_id = 'fooValue'
     * $query->filterByPlanId('%fooValue%'); // WHERE plan_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $planId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByPlanId($planId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($planId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $planId)) {
                $planId = str_replace('*', '%', $planId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::PLAN_ID, $planId, $comparison);
    }

    /**
     * Filter the query on the state column
     *
     * Example usage:
     * <code>
     * $query->filterByState('fooValue');   // WHERE state = 'fooValue'
     * $query->filterByState('%fooValue%'); // WHERE state LIKE '%fooValue%'
     * </code>
     *
     * @param     string $state The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByState($state = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($state)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $state)) {
                $state = str_replace('*', '%', $state);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::STATE, $state, $comparison);
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
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(PaypalPlanTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(PaypalPlanTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::CREATED_AT, $createdAt, $comparison);
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
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(PaypalPlanTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(PaypalPlanTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalPlanTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \PayPal\Model\PaypalOrder object
     *
     * @param \PayPal\Model\PaypalOrder|ObjectCollection $paypalOrder The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function filterByPaypalOrder($paypalOrder, $comparison = null)
    {
        if ($paypalOrder instanceof \PayPal\Model\PaypalOrder) {
            return $this
                ->addUsingAlias(PaypalPlanTableMap::PAYPAL_ORDER_ID, $paypalOrder->getId(), $comparison);
        } elseif ($paypalOrder instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalPlanTableMap::PAYPAL_ORDER_ID, $paypalOrder->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByPaypalOrder() only accepts arguments of type \PayPal\Model\PaypalOrder or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PaypalOrder relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function joinPaypalOrder($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PaypalOrder');

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
            $this->addJoinObject($join, 'PaypalOrder');
        }

        return $this;
    }

    /**
     * Use the PaypalOrder relation PaypalOrder object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \PayPal\Model\PaypalOrderQuery A secondary query class using the current class as primary query
     */
    public function usePaypalOrderQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPaypalOrder($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PaypalOrder', '\PayPal\Model\PaypalOrderQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPaypalPlan $paypalPlan Object to remove from the list of results
     *
     * @return ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function prune($paypalPlan = null)
    {
        if ($paypalPlan) {
            $this->addUsingAlias(PaypalPlanTableMap::ID, $paypalPlan->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the paypal_plan table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalPlanTableMap::DATABASE_NAME);
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
            PaypalPlanTableMap::clearInstancePool();
            PaypalPlanTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildPaypalPlan or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildPaypalPlan object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalPlanTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PaypalPlanTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        PaypalPlanTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PaypalPlanTableMap::clearRelatedInstancePool();
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
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalPlanTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalPlanTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalPlanTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalPlanTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalPlanTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildPaypalPlanQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalPlanTableMap::CREATED_AT);
    }

} // PaypalPlanQuery
