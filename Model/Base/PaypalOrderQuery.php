<?php

namespace PayPal\Model\Base;

use \Exception;
use \PDO;
use PayPal\Model\PaypalOrder as ChildPaypalOrder;
use PayPal\Model\PaypalOrderQuery as ChildPaypalOrderQuery;
use PayPal\Model\Map\PaypalOrderTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Order;

/**
 * Base class that represents a query for the 'paypal_order' table.
 *
 *
 *
 * @method     ChildPaypalOrderQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPaypalOrderQuery orderByPaymentId($order = Criteria::ASC) Order by the payment_id column
 * @method     ChildPaypalOrderQuery orderByAgreementId($order = Criteria::ASC) Order by the agreement_id column
 * @method     ChildPaypalOrderQuery orderByCreditCardId($order = Criteria::ASC) Order by the credit_card_id column
 * @method     ChildPaypalOrderQuery orderByState($order = Criteria::ASC) Order by the state column
 * @method     ChildPaypalOrderQuery orderByAmount($order = Criteria::ASC) Order by the amount column
 * @method     ChildPaypalOrderQuery orderByDescription($order = Criteria::ASC) Order by the description column
 * @method     ChildPaypalOrderQuery orderByPayerId($order = Criteria::ASC) Order by the payer_id column
 * @method     ChildPaypalOrderQuery orderByToken($order = Criteria::ASC) Order by the token column
 * @method     ChildPaypalOrderQuery orderByPlanifiedTitle($order = Criteria::ASC) Order by the planified_title column
 * @method     ChildPaypalOrderQuery orderByPlanifiedDescription($order = Criteria::ASC) Order by the planified_description column
 * @method     ChildPaypalOrderQuery orderByPlanifiedFrequency($order = Criteria::ASC) Order by the planified_frequency column
 * @method     ChildPaypalOrderQuery orderByPlanifiedFrequencyInterval($order = Criteria::ASC) Order by the planified_frequency_interval column
 * @method     ChildPaypalOrderQuery orderByPlanifiedCycle($order = Criteria::ASC) Order by the planified_cycle column
 * @method     ChildPaypalOrderQuery orderByPlanifiedActualCycle($order = Criteria::ASC) Order by the planified_actual_cycle column
 * @method     ChildPaypalOrderQuery orderByPlanifiedMinAmount($order = Criteria::ASC) Order by the planified_min_amount column
 * @method     ChildPaypalOrderQuery orderByPlanifiedMaxAmount($order = Criteria::ASC) Order by the planified_max_amount column
 * @method     ChildPaypalOrderQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildPaypalOrderQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 * @method     ChildPaypalOrderQuery orderByVersion($order = Criteria::ASC) Order by the version column
 * @method     ChildPaypalOrderQuery orderByVersionCreatedAt($order = Criteria::ASC) Order by the version_created_at column
 * @method     ChildPaypalOrderQuery orderByVersionCreatedBy($order = Criteria::ASC) Order by the version_created_by column
 *
 * @method     ChildPaypalOrderQuery groupById() Group by the id column
 * @method     ChildPaypalOrderQuery groupByPaymentId() Group by the payment_id column
 * @method     ChildPaypalOrderQuery groupByAgreementId() Group by the agreement_id column
 * @method     ChildPaypalOrderQuery groupByCreditCardId() Group by the credit_card_id column
 * @method     ChildPaypalOrderQuery groupByState() Group by the state column
 * @method     ChildPaypalOrderQuery groupByAmount() Group by the amount column
 * @method     ChildPaypalOrderQuery groupByDescription() Group by the description column
 * @method     ChildPaypalOrderQuery groupByPayerId() Group by the payer_id column
 * @method     ChildPaypalOrderQuery groupByToken() Group by the token column
 * @method     ChildPaypalOrderQuery groupByPlanifiedTitle() Group by the planified_title column
 * @method     ChildPaypalOrderQuery groupByPlanifiedDescription() Group by the planified_description column
 * @method     ChildPaypalOrderQuery groupByPlanifiedFrequency() Group by the planified_frequency column
 * @method     ChildPaypalOrderQuery groupByPlanifiedFrequencyInterval() Group by the planified_frequency_interval column
 * @method     ChildPaypalOrderQuery groupByPlanifiedCycle() Group by the planified_cycle column
 * @method     ChildPaypalOrderQuery groupByPlanifiedActualCycle() Group by the planified_actual_cycle column
 * @method     ChildPaypalOrderQuery groupByPlanifiedMinAmount() Group by the planified_min_amount column
 * @method     ChildPaypalOrderQuery groupByPlanifiedMaxAmount() Group by the planified_max_amount column
 * @method     ChildPaypalOrderQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildPaypalOrderQuery groupByUpdatedAt() Group by the updated_at column
 * @method     ChildPaypalOrderQuery groupByVersion() Group by the version column
 * @method     ChildPaypalOrderQuery groupByVersionCreatedAt() Group by the version_created_at column
 * @method     ChildPaypalOrderQuery groupByVersionCreatedBy() Group by the version_created_by column
 *
 * @method     ChildPaypalOrderQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPaypalOrderQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPaypalOrderQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPaypalOrderQuery leftJoinOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the Order relation
 * @method     ChildPaypalOrderQuery rightJoinOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Order relation
 * @method     ChildPaypalOrderQuery innerJoinOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the Order relation
 *
 * @method     ChildPaypalOrderQuery leftJoinPaypalPlan($relationAlias = null) Adds a LEFT JOIN clause to the query using the PaypalPlan relation
 * @method     ChildPaypalOrderQuery rightJoinPaypalPlan($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PaypalPlan relation
 * @method     ChildPaypalOrderQuery innerJoinPaypalPlan($relationAlias = null) Adds a INNER JOIN clause to the query using the PaypalPlan relation
 *
 * @method     ChildPaypalOrderQuery leftJoinPaypalOrderVersion($relationAlias = null) Adds a LEFT JOIN clause to the query using the PaypalOrderVersion relation
 * @method     ChildPaypalOrderQuery rightJoinPaypalOrderVersion($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PaypalOrderVersion relation
 * @method     ChildPaypalOrderQuery innerJoinPaypalOrderVersion($relationAlias = null) Adds a INNER JOIN clause to the query using the PaypalOrderVersion relation
 *
 * @method     ChildPaypalOrder findOne(ConnectionInterface $con = null) Return the first ChildPaypalOrder matching the query
 * @method     ChildPaypalOrder findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPaypalOrder matching the query, or a new ChildPaypalOrder object populated from the query conditions when no match is found
 *
 * @method     ChildPaypalOrder findOneById(int $id) Return the first ChildPaypalOrder filtered by the id column
 * @method     ChildPaypalOrder findOneByPaymentId(string $payment_id) Return the first ChildPaypalOrder filtered by the payment_id column
 * @method     ChildPaypalOrder findOneByAgreementId(string $agreement_id) Return the first ChildPaypalOrder filtered by the agreement_id column
 * @method     ChildPaypalOrder findOneByCreditCardId(string $credit_card_id) Return the first ChildPaypalOrder filtered by the credit_card_id column
 * @method     ChildPaypalOrder findOneByState(string $state) Return the first ChildPaypalOrder filtered by the state column
 * @method     ChildPaypalOrder findOneByAmount(string $amount) Return the first ChildPaypalOrder filtered by the amount column
 * @method     ChildPaypalOrder findOneByDescription(string $description) Return the first ChildPaypalOrder filtered by the description column
 * @method     ChildPaypalOrder findOneByPayerId(string $payer_id) Return the first ChildPaypalOrder filtered by the payer_id column
 * @method     ChildPaypalOrder findOneByToken(string $token) Return the first ChildPaypalOrder filtered by the token column
 * @method     ChildPaypalOrder findOneByPlanifiedTitle(string $planified_title) Return the first ChildPaypalOrder filtered by the planified_title column
 * @method     ChildPaypalOrder findOneByPlanifiedDescription(string $planified_description) Return the first ChildPaypalOrder filtered by the planified_description column
 * @method     ChildPaypalOrder findOneByPlanifiedFrequency(string $planified_frequency) Return the first ChildPaypalOrder filtered by the planified_frequency column
 * @method     ChildPaypalOrder findOneByPlanifiedFrequencyInterval(int $planified_frequency_interval) Return the first ChildPaypalOrder filtered by the planified_frequency_interval column
 * @method     ChildPaypalOrder findOneByPlanifiedCycle(int $planified_cycle) Return the first ChildPaypalOrder filtered by the planified_cycle column
 * @method     ChildPaypalOrder findOneByPlanifiedActualCycle(int $planified_actual_cycle) Return the first ChildPaypalOrder filtered by the planified_actual_cycle column
 * @method     ChildPaypalOrder findOneByPlanifiedMinAmount(string $planified_min_amount) Return the first ChildPaypalOrder filtered by the planified_min_amount column
 * @method     ChildPaypalOrder findOneByPlanifiedMaxAmount(string $planified_max_amount) Return the first ChildPaypalOrder filtered by the planified_max_amount column
 * @method     ChildPaypalOrder findOneByCreatedAt(string $created_at) Return the first ChildPaypalOrder filtered by the created_at column
 * @method     ChildPaypalOrder findOneByUpdatedAt(string $updated_at) Return the first ChildPaypalOrder filtered by the updated_at column
 * @method     ChildPaypalOrder findOneByVersion(int $version) Return the first ChildPaypalOrder filtered by the version column
 * @method     ChildPaypalOrder findOneByVersionCreatedAt(string $version_created_at) Return the first ChildPaypalOrder filtered by the version_created_at column
 * @method     ChildPaypalOrder findOneByVersionCreatedBy(string $version_created_by) Return the first ChildPaypalOrder filtered by the version_created_by column
 *
 * @method     array findById(int $id) Return ChildPaypalOrder objects filtered by the id column
 * @method     array findByPaymentId(string $payment_id) Return ChildPaypalOrder objects filtered by the payment_id column
 * @method     array findByAgreementId(string $agreement_id) Return ChildPaypalOrder objects filtered by the agreement_id column
 * @method     array findByCreditCardId(string $credit_card_id) Return ChildPaypalOrder objects filtered by the credit_card_id column
 * @method     array findByState(string $state) Return ChildPaypalOrder objects filtered by the state column
 * @method     array findByAmount(string $amount) Return ChildPaypalOrder objects filtered by the amount column
 * @method     array findByDescription(string $description) Return ChildPaypalOrder objects filtered by the description column
 * @method     array findByPayerId(string $payer_id) Return ChildPaypalOrder objects filtered by the payer_id column
 * @method     array findByToken(string $token) Return ChildPaypalOrder objects filtered by the token column
 * @method     array findByPlanifiedTitle(string $planified_title) Return ChildPaypalOrder objects filtered by the planified_title column
 * @method     array findByPlanifiedDescription(string $planified_description) Return ChildPaypalOrder objects filtered by the planified_description column
 * @method     array findByPlanifiedFrequency(string $planified_frequency) Return ChildPaypalOrder objects filtered by the planified_frequency column
 * @method     array findByPlanifiedFrequencyInterval(int $planified_frequency_interval) Return ChildPaypalOrder objects filtered by the planified_frequency_interval column
 * @method     array findByPlanifiedCycle(int $planified_cycle) Return ChildPaypalOrder objects filtered by the planified_cycle column
 * @method     array findByPlanifiedActualCycle(int $planified_actual_cycle) Return ChildPaypalOrder objects filtered by the planified_actual_cycle column
 * @method     array findByPlanifiedMinAmount(string $planified_min_amount) Return ChildPaypalOrder objects filtered by the planified_min_amount column
 * @method     array findByPlanifiedMaxAmount(string $planified_max_amount) Return ChildPaypalOrder objects filtered by the planified_max_amount column
 * @method     array findByCreatedAt(string $created_at) Return ChildPaypalOrder objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildPaypalOrder objects filtered by the updated_at column
 * @method     array findByVersion(int $version) Return ChildPaypalOrder objects filtered by the version column
 * @method     array findByVersionCreatedAt(string $version_created_at) Return ChildPaypalOrder objects filtered by the version_created_at column
 * @method     array findByVersionCreatedBy(string $version_created_by) Return ChildPaypalOrder objects filtered by the version_created_by column
 *
 */
abstract class PaypalOrderQuery extends ModelCriteria
{

    // versionable behavior

    /**
     * Whether the versioning is enabled
     */
    static $isVersioningEnabled = true;

    /**
     * Initializes internal state of \PayPal\Model\Base\PaypalOrderQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\PayPal\\Model\\PaypalOrder', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPaypalOrderQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPaypalOrderQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \PayPal\Model\PaypalOrderQuery) {
            return $criteria;
        }
        $query = new \PayPal\Model\PaypalOrderQuery();
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
     * @return ChildPaypalOrder|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaypalOrderTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalOrderTableMap::DATABASE_NAME);
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
     * @return   ChildPaypalOrder A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PAYMENT_ID, AGREEMENT_ID, CREDIT_CARD_ID, STATE, AMOUNT, DESCRIPTION, PAYER_ID, TOKEN, PLANIFIED_TITLE, PLANIFIED_DESCRIPTION, PLANIFIED_FREQUENCY, PLANIFIED_FREQUENCY_INTERVAL, PLANIFIED_CYCLE, PLANIFIED_ACTUAL_CYCLE, PLANIFIED_MIN_AMOUNT, PLANIFIED_MAX_AMOUNT, CREATED_AT, UPDATED_AT, VERSION, VERSION_CREATED_AT, VERSION_CREATED_BY FROM paypal_order WHERE ID = :p0';
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
            $obj = new ChildPaypalOrder();
            $obj->hydrate($row);
            PaypalOrderTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildPaypalOrder|array|mixed the result, formatted by the current formatter
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
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PaypalOrderTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PaypalOrderTableMap::ID, $keys, Criteria::IN);
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
     * @see       filterByOrder()
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the payment_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPaymentId('fooValue');   // WHERE payment_id = 'fooValue'
     * $query->filterByPaymentId('%fooValue%'); // WHERE payment_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $paymentId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPaymentId($paymentId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($paymentId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $paymentId)) {
                $paymentId = str_replace('*', '%', $paymentId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PAYMENT_ID, $paymentId, $comparison);
    }

    /**
     * Filter the query on the agreement_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAgreementId('fooValue');   // WHERE agreement_id = 'fooValue'
     * $query->filterByAgreementId('%fooValue%'); // WHERE agreement_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $agreementId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByAgreementId($agreementId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($agreementId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $agreementId)) {
                $agreementId = str_replace('*', '%', $agreementId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::AGREEMENT_ID, $agreementId, $comparison);
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
     * @return ChildPaypalOrderQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderTableMap::CREDIT_CARD_ID, $creditCardId, $comparison);
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
     * @return ChildPaypalOrderQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderTableMap::STATE, $state, $comparison);
    }

    /**
     * Filter the query on the amount column
     *
     * Example usage:
     * <code>
     * $query->filterByAmount(1234); // WHERE amount = 1234
     * $query->filterByAmount(array(12, 34)); // WHERE amount IN (12, 34)
     * $query->filterByAmount(array('min' => 12)); // WHERE amount > 12
     * </code>
     *
     * @param     mixed $amount The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByAmount($amount = null, $comparison = null)
    {
        if (is_array($amount)) {
            $useMinMax = false;
            if (isset($amount['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::AMOUNT, $amount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($amount['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::AMOUNT, $amount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::AMOUNT, $amount, $comparison);
    }

    /**
     * Filter the query on the description column
     *
     * Example usage:
     * <code>
     * $query->filterByDescription('fooValue');   // WHERE description = 'fooValue'
     * $query->filterByDescription('%fooValue%'); // WHERE description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $description The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByDescription($description = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($description)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $description)) {
                $description = str_replace('*', '%', $description);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::DESCRIPTION, $description, $comparison);
    }

    /**
     * Filter the query on the payer_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPayerId('fooValue');   // WHERE payer_id = 'fooValue'
     * $query->filterByPayerId('%fooValue%'); // WHERE payer_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $payerId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPayerId($payerId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($payerId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $payerId)) {
                $payerId = str_replace('*', '%', $payerId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PAYER_ID, $payerId, $comparison);
    }

    /**
     * Filter the query on the token column
     *
     * Example usage:
     * <code>
     * $query->filterByToken('fooValue');   // WHERE token = 'fooValue'
     * $query->filterByToken('%fooValue%'); // WHERE token LIKE '%fooValue%'
     * </code>
     *
     * @param     string $token The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByToken($token = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($token)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $token)) {
                $token = str_replace('*', '%', $token);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::TOKEN, $token, $comparison);
    }

    /**
     * Filter the query on the planified_title column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedTitle('fooValue');   // WHERE planified_title = 'fooValue'
     * $query->filterByPlanifiedTitle('%fooValue%'); // WHERE planified_title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $planifiedTitle The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedTitle($planifiedTitle = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($planifiedTitle)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $planifiedTitle)) {
                $planifiedTitle = str_replace('*', '%', $planifiedTitle);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_TITLE, $planifiedTitle, $comparison);
    }

    /**
     * Filter the query on the planified_description column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedDescription('fooValue');   // WHERE planified_description = 'fooValue'
     * $query->filterByPlanifiedDescription('%fooValue%'); // WHERE planified_description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $planifiedDescription The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedDescription($planifiedDescription = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($planifiedDescription)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $planifiedDescription)) {
                $planifiedDescription = str_replace('*', '%', $planifiedDescription);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_DESCRIPTION, $planifiedDescription, $comparison);
    }

    /**
     * Filter the query on the planified_frequency column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedFrequency('fooValue');   // WHERE planified_frequency = 'fooValue'
     * $query->filterByPlanifiedFrequency('%fooValue%'); // WHERE planified_frequency LIKE '%fooValue%'
     * </code>
     *
     * @param     string $planifiedFrequency The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedFrequency($planifiedFrequency = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($planifiedFrequency)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $planifiedFrequency)) {
                $planifiedFrequency = str_replace('*', '%', $planifiedFrequency);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_FREQUENCY, $planifiedFrequency, $comparison);
    }

    /**
     * Filter the query on the planified_frequency_interval column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedFrequencyInterval(1234); // WHERE planified_frequency_interval = 1234
     * $query->filterByPlanifiedFrequencyInterval(array(12, 34)); // WHERE planified_frequency_interval IN (12, 34)
     * $query->filterByPlanifiedFrequencyInterval(array('min' => 12)); // WHERE planified_frequency_interval > 12
     * </code>
     *
     * @param     mixed $planifiedFrequencyInterval The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedFrequencyInterval($planifiedFrequencyInterval = null, $comparison = null)
    {
        if (is_array($planifiedFrequencyInterval)) {
            $useMinMax = false;
            if (isset($planifiedFrequencyInterval['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedFrequencyInterval['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval, $comparison);
    }

    /**
     * Filter the query on the planified_cycle column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedCycle(1234); // WHERE planified_cycle = 1234
     * $query->filterByPlanifiedCycle(array(12, 34)); // WHERE planified_cycle IN (12, 34)
     * $query->filterByPlanifiedCycle(array('min' => 12)); // WHERE planified_cycle > 12
     * </code>
     *
     * @param     mixed $planifiedCycle The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedCycle($planifiedCycle = null, $comparison = null)
    {
        if (is_array($planifiedCycle)) {
            $useMinMax = false;
            if (isset($planifiedCycle['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_CYCLE, $planifiedCycle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedCycle['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_CYCLE, $planifiedCycle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_CYCLE, $planifiedCycle, $comparison);
    }

    /**
     * Filter the query on the planified_actual_cycle column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedActualCycle(1234); // WHERE planified_actual_cycle = 1234
     * $query->filterByPlanifiedActualCycle(array(12, 34)); // WHERE planified_actual_cycle IN (12, 34)
     * $query->filterByPlanifiedActualCycle(array('min' => 12)); // WHERE planified_actual_cycle > 12
     * </code>
     *
     * @param     mixed $planifiedActualCycle The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedActualCycle($planifiedActualCycle = null, $comparison = null)
    {
        if (is_array($planifiedActualCycle)) {
            $useMinMax = false;
            if (isset($planifiedActualCycle['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedActualCycle['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle, $comparison);
    }

    /**
     * Filter the query on the planified_min_amount column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedMinAmount(1234); // WHERE planified_min_amount = 1234
     * $query->filterByPlanifiedMinAmount(array(12, 34)); // WHERE planified_min_amount IN (12, 34)
     * $query->filterByPlanifiedMinAmount(array('min' => 12)); // WHERE planified_min_amount > 12
     * </code>
     *
     * @param     mixed $planifiedMinAmount The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedMinAmount($planifiedMinAmount = null, $comparison = null)
    {
        if (is_array($planifiedMinAmount)) {
            $useMinMax = false;
            if (isset($planifiedMinAmount['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedMinAmount['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount, $comparison);
    }

    /**
     * Filter the query on the planified_max_amount column
     *
     * Example usage:
     * <code>
     * $query->filterByPlanifiedMaxAmount(1234); // WHERE planified_max_amount = 1234
     * $query->filterByPlanifiedMaxAmount(array(12, 34)); // WHERE planified_max_amount IN (12, 34)
     * $query->filterByPlanifiedMaxAmount(array('min' => 12)); // WHERE planified_max_amount > 12
     * </code>
     *
     * @param     mixed $planifiedMaxAmount The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPlanifiedMaxAmount($planifiedMaxAmount = null, $comparison = null)
    {
        if (is_array($planifiedMaxAmount)) {
            $useMinMax = false;
            if (isset($planifiedMaxAmount['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedMaxAmount['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount, $comparison);
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
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::CREATED_AT, $createdAt, $comparison);
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
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query on the version column
     *
     * Example usage:
     * <code>
     * $query->filterByVersion(1234); // WHERE version = 1234
     * $query->filterByVersion(array(12, 34)); // WHERE version IN (12, 34)
     * $query->filterByVersion(array('min' => 12)); // WHERE version > 12
     * </code>
     *
     * @param     mixed $version The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByVersion($version = null, $comparison = null)
    {
        if (is_array($version)) {
            $useMinMax = false;
            if (isset($version['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::VERSION, $version['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($version['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::VERSION, $version['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::VERSION, $version, $comparison);
    }

    /**
     * Filter the query on the version_created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByVersionCreatedAt('2011-03-14'); // WHERE version_created_at = '2011-03-14'
     * $query->filterByVersionCreatedAt('now'); // WHERE version_created_at = '2011-03-14'
     * $query->filterByVersionCreatedAt(array('max' => 'yesterday')); // WHERE version_created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $versionCreatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByVersionCreatedAt($versionCreatedAt = null, $comparison = null)
    {
        if (is_array($versionCreatedAt)) {
            $useMinMax = false;
            if (isset($versionCreatedAt['min'])) {
                $this->addUsingAlias(PaypalOrderTableMap::VERSION_CREATED_AT, $versionCreatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($versionCreatedAt['max'])) {
                $this->addUsingAlias(PaypalOrderTableMap::VERSION_CREATED_AT, $versionCreatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::VERSION_CREATED_AT, $versionCreatedAt, $comparison);
    }

    /**
     * Filter the query on the version_created_by column
     *
     * Example usage:
     * <code>
     * $query->filterByVersionCreatedBy('fooValue');   // WHERE version_created_by = 'fooValue'
     * $query->filterByVersionCreatedBy('%fooValue%'); // WHERE version_created_by LIKE '%fooValue%'
     * </code>
     *
     * @param     string $versionCreatedBy The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByVersionCreatedBy($versionCreatedBy = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($versionCreatedBy)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $versionCreatedBy)) {
                $versionCreatedBy = str_replace('*', '%', $versionCreatedBy);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalOrderTableMap::VERSION_CREATED_BY, $versionCreatedBy, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\Order object
     *
     * @param \Thelia\Model\Order|ObjectCollection $order The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByOrder($order, $comparison = null)
    {
        if ($order instanceof \Thelia\Model\Order) {
            return $this
                ->addUsingAlias(PaypalOrderTableMap::ID, $order->getId(), $comparison);
        } elseif ($order instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalOrderTableMap::ID, $order->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByOrder() only accepts arguments of type \Thelia\Model\Order or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Order relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function joinOrder($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Order');

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
            $this->addJoinObject($join, 'Order');
        }

        return $this;
    }

    /**
     * Use the Order relation Order object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\OrderQuery A secondary query class using the current class as primary query
     */
    public function useOrderQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinOrder($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Order', '\Thelia\Model\OrderQuery');
    }

    /**
     * Filter the query by a related \PayPal\Model\PaypalPlan object
     *
     * @param \PayPal\Model\PaypalPlan|ObjectCollection $paypalPlan  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPaypalPlan($paypalPlan, $comparison = null)
    {
        if ($paypalPlan instanceof \PayPal\Model\PaypalPlan) {
            return $this
                ->addUsingAlias(PaypalOrderTableMap::ID, $paypalPlan->getPaypalOrderId(), $comparison);
        } elseif ($paypalPlan instanceof ObjectCollection) {
            return $this
                ->usePaypalPlanQuery()
                ->filterByPrimaryKeys($paypalPlan->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPaypalPlan() only accepts arguments of type \PayPal\Model\PaypalPlan or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PaypalPlan relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function joinPaypalPlan($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PaypalPlan');

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
            $this->addJoinObject($join, 'PaypalPlan');
        }

        return $this;
    }

    /**
     * Use the PaypalPlan relation PaypalPlan object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \PayPal\Model\PaypalPlanQuery A secondary query class using the current class as primary query
     */
    public function usePaypalPlanQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPaypalPlan($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PaypalPlan', '\PayPal\Model\PaypalPlanQuery');
    }

    /**
     * Filter the query by a related \PayPal\Model\PaypalOrderVersion object
     *
     * @param \PayPal\Model\PaypalOrderVersion|ObjectCollection $paypalOrderVersion  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function filterByPaypalOrderVersion($paypalOrderVersion, $comparison = null)
    {
        if ($paypalOrderVersion instanceof \PayPal\Model\PaypalOrderVersion) {
            return $this
                ->addUsingAlias(PaypalOrderTableMap::ID, $paypalOrderVersion->getId(), $comparison);
        } elseif ($paypalOrderVersion instanceof ObjectCollection) {
            return $this
                ->usePaypalOrderVersionQuery()
                ->filterByPrimaryKeys($paypalOrderVersion->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPaypalOrderVersion() only accepts arguments of type \PayPal\Model\PaypalOrderVersion or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PaypalOrderVersion relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function joinPaypalOrderVersion($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PaypalOrderVersion');

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
            $this->addJoinObject($join, 'PaypalOrderVersion');
        }

        return $this;
    }

    /**
     * Use the PaypalOrderVersion relation PaypalOrderVersion object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \PayPal\Model\PaypalOrderVersionQuery A secondary query class using the current class as primary query
     */
    public function usePaypalOrderVersionQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPaypalOrderVersion($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PaypalOrderVersion', '\PayPal\Model\PaypalOrderVersionQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPaypalOrder $paypalOrder Object to remove from the list of results
     *
     * @return ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function prune($paypalOrder = null)
    {
        if ($paypalOrder) {
            $this->addUsingAlias(PaypalOrderTableMap::ID, $paypalOrder->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the paypal_order table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderTableMap::DATABASE_NAME);
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
            PaypalOrderTableMap::clearInstancePool();
            PaypalOrderTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildPaypalOrder or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildPaypalOrder object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PaypalOrderTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        PaypalOrderTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PaypalOrderTableMap::clearRelatedInstancePool();
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
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalOrderTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalOrderTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalOrderTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalOrderTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalOrderTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildPaypalOrderQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalOrderTableMap::CREATED_AT);
    }

    // versionable behavior

    /**
     * Checks whether versioning is enabled
     *
     * @return boolean
     */
    static public function isVersioningEnabled()
    {
        return self::$isVersioningEnabled;
    }

    /**
     * Enables versioning
     */
    static public function enableVersioning()
    {
        self::$isVersioningEnabled = true;
    }

    /**
     * Disables versioning
     */
    static public function disableVersioning()
    {
        self::$isVersioningEnabled = false;
    }

} // PaypalOrderQuery
