<?php

namespace PayPal\Model\Base;

use \Exception;
use \PDO;
use PayPal\Model\PaypalOrderVersion as ChildPaypalOrderVersion;
use PayPal\Model\PaypalOrderVersionQuery as ChildPaypalOrderVersionQuery;
use PayPal\Model\Map\PaypalOrderVersionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'paypal_order_version' table.
 *
 *
 *
 * @method     ChildPaypalOrderVersionQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPaypalOrderVersionQuery orderByPaymentId($order = Criteria::ASC) Order by the payment_id column
 * @method     ChildPaypalOrderVersionQuery orderByAgreementId($order = Criteria::ASC) Order by the agreement_id column
 * @method     ChildPaypalOrderVersionQuery orderByCreditCardId($order = Criteria::ASC) Order by the credit_card_id column
 * @method     ChildPaypalOrderVersionQuery orderByState($order = Criteria::ASC) Order by the state column
 * @method     ChildPaypalOrderVersionQuery orderByAmount($order = Criteria::ASC) Order by the amount column
 * @method     ChildPaypalOrderVersionQuery orderByDescription($order = Criteria::ASC) Order by the description column
 * @method     ChildPaypalOrderVersionQuery orderByPayerId($order = Criteria::ASC) Order by the payer_id column
 * @method     ChildPaypalOrderVersionQuery orderByToken($order = Criteria::ASC) Order by the token column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedTitle($order = Criteria::ASC) Order by the planified_title column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedDescription($order = Criteria::ASC) Order by the planified_description column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedFrequency($order = Criteria::ASC) Order by the planified_frequency column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedFrequencyInterval($order = Criteria::ASC) Order by the planified_frequency_interval column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedCycle($order = Criteria::ASC) Order by the planified_cycle column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedActualCycle($order = Criteria::ASC) Order by the planified_actual_cycle column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedMinAmount($order = Criteria::ASC) Order by the planified_min_amount column
 * @method     ChildPaypalOrderVersionQuery orderByPlanifiedMaxAmount($order = Criteria::ASC) Order by the planified_max_amount column
 * @method     ChildPaypalOrderVersionQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildPaypalOrderVersionQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 * @method     ChildPaypalOrderVersionQuery orderByVersion($order = Criteria::ASC) Order by the version column
 * @method     ChildPaypalOrderVersionQuery orderByVersionCreatedAt($order = Criteria::ASC) Order by the version_created_at column
 * @method     ChildPaypalOrderVersionQuery orderByVersionCreatedBy($order = Criteria::ASC) Order by the version_created_by column
 * @method     ChildPaypalOrderVersionQuery orderByIdVersion($order = Criteria::ASC) Order by the id_version column
 *
 * @method     ChildPaypalOrderVersionQuery groupById() Group by the id column
 * @method     ChildPaypalOrderVersionQuery groupByPaymentId() Group by the payment_id column
 * @method     ChildPaypalOrderVersionQuery groupByAgreementId() Group by the agreement_id column
 * @method     ChildPaypalOrderVersionQuery groupByCreditCardId() Group by the credit_card_id column
 * @method     ChildPaypalOrderVersionQuery groupByState() Group by the state column
 * @method     ChildPaypalOrderVersionQuery groupByAmount() Group by the amount column
 * @method     ChildPaypalOrderVersionQuery groupByDescription() Group by the description column
 * @method     ChildPaypalOrderVersionQuery groupByPayerId() Group by the payer_id column
 * @method     ChildPaypalOrderVersionQuery groupByToken() Group by the token column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedTitle() Group by the planified_title column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedDescription() Group by the planified_description column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedFrequency() Group by the planified_frequency column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedFrequencyInterval() Group by the planified_frequency_interval column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedCycle() Group by the planified_cycle column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedActualCycle() Group by the planified_actual_cycle column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedMinAmount() Group by the planified_min_amount column
 * @method     ChildPaypalOrderVersionQuery groupByPlanifiedMaxAmount() Group by the planified_max_amount column
 * @method     ChildPaypalOrderVersionQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildPaypalOrderVersionQuery groupByUpdatedAt() Group by the updated_at column
 * @method     ChildPaypalOrderVersionQuery groupByVersion() Group by the version column
 * @method     ChildPaypalOrderVersionQuery groupByVersionCreatedAt() Group by the version_created_at column
 * @method     ChildPaypalOrderVersionQuery groupByVersionCreatedBy() Group by the version_created_by column
 * @method     ChildPaypalOrderVersionQuery groupByIdVersion() Group by the id_version column
 *
 * @method     ChildPaypalOrderVersionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPaypalOrderVersionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPaypalOrderVersionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPaypalOrderVersionQuery leftJoinPaypalOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the PaypalOrder relation
 * @method     ChildPaypalOrderVersionQuery rightJoinPaypalOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PaypalOrder relation
 * @method     ChildPaypalOrderVersionQuery innerJoinPaypalOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the PaypalOrder relation
 *
 * @method     ChildPaypalOrderVersion findOne(ConnectionInterface $con = null) Return the first ChildPaypalOrderVersion matching the query
 * @method     ChildPaypalOrderVersion findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPaypalOrderVersion matching the query, or a new ChildPaypalOrderVersion object populated from the query conditions when no match is found
 *
 * @method     ChildPaypalOrderVersion findOneById(int $id) Return the first ChildPaypalOrderVersion filtered by the id column
 * @method     ChildPaypalOrderVersion findOneByPaymentId(string $payment_id) Return the first ChildPaypalOrderVersion filtered by the payment_id column
 * @method     ChildPaypalOrderVersion findOneByAgreementId(string $agreement_id) Return the first ChildPaypalOrderVersion filtered by the agreement_id column
 * @method     ChildPaypalOrderVersion findOneByCreditCardId(string $credit_card_id) Return the first ChildPaypalOrderVersion filtered by the credit_card_id column
 * @method     ChildPaypalOrderVersion findOneByState(string $state) Return the first ChildPaypalOrderVersion filtered by the state column
 * @method     ChildPaypalOrderVersion findOneByAmount(string $amount) Return the first ChildPaypalOrderVersion filtered by the amount column
 * @method     ChildPaypalOrderVersion findOneByDescription(string $description) Return the first ChildPaypalOrderVersion filtered by the description column
 * @method     ChildPaypalOrderVersion findOneByPayerId(string $payer_id) Return the first ChildPaypalOrderVersion filtered by the payer_id column
 * @method     ChildPaypalOrderVersion findOneByToken(string $token) Return the first ChildPaypalOrderVersion filtered by the token column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedTitle(string $planified_title) Return the first ChildPaypalOrderVersion filtered by the planified_title column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedDescription(string $planified_description) Return the first ChildPaypalOrderVersion filtered by the planified_description column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedFrequency(string $planified_frequency) Return the first ChildPaypalOrderVersion filtered by the planified_frequency column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedFrequencyInterval(int $planified_frequency_interval) Return the first ChildPaypalOrderVersion filtered by the planified_frequency_interval column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedCycle(int $planified_cycle) Return the first ChildPaypalOrderVersion filtered by the planified_cycle column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedActualCycle(int $planified_actual_cycle) Return the first ChildPaypalOrderVersion filtered by the planified_actual_cycle column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedMinAmount(string $planified_min_amount) Return the first ChildPaypalOrderVersion filtered by the planified_min_amount column
 * @method     ChildPaypalOrderVersion findOneByPlanifiedMaxAmount(string $planified_max_amount) Return the first ChildPaypalOrderVersion filtered by the planified_max_amount column
 * @method     ChildPaypalOrderVersion findOneByCreatedAt(string $created_at) Return the first ChildPaypalOrderVersion filtered by the created_at column
 * @method     ChildPaypalOrderVersion findOneByUpdatedAt(string $updated_at) Return the first ChildPaypalOrderVersion filtered by the updated_at column
 * @method     ChildPaypalOrderVersion findOneByVersion(int $version) Return the first ChildPaypalOrderVersion filtered by the version column
 * @method     ChildPaypalOrderVersion findOneByVersionCreatedAt(string $version_created_at) Return the first ChildPaypalOrderVersion filtered by the version_created_at column
 * @method     ChildPaypalOrderVersion findOneByVersionCreatedBy(string $version_created_by) Return the first ChildPaypalOrderVersion filtered by the version_created_by column
 * @method     ChildPaypalOrderVersion findOneByIdVersion(int $id_version) Return the first ChildPaypalOrderVersion filtered by the id_version column
 *
 * @method     array findById(int $id) Return ChildPaypalOrderVersion objects filtered by the id column
 * @method     array findByPaymentId(string $payment_id) Return ChildPaypalOrderVersion objects filtered by the payment_id column
 * @method     array findByAgreementId(string $agreement_id) Return ChildPaypalOrderVersion objects filtered by the agreement_id column
 * @method     array findByCreditCardId(string $credit_card_id) Return ChildPaypalOrderVersion objects filtered by the credit_card_id column
 * @method     array findByState(string $state) Return ChildPaypalOrderVersion objects filtered by the state column
 * @method     array findByAmount(string $amount) Return ChildPaypalOrderVersion objects filtered by the amount column
 * @method     array findByDescription(string $description) Return ChildPaypalOrderVersion objects filtered by the description column
 * @method     array findByPayerId(string $payer_id) Return ChildPaypalOrderVersion objects filtered by the payer_id column
 * @method     array findByToken(string $token) Return ChildPaypalOrderVersion objects filtered by the token column
 * @method     array findByPlanifiedTitle(string $planified_title) Return ChildPaypalOrderVersion objects filtered by the planified_title column
 * @method     array findByPlanifiedDescription(string $planified_description) Return ChildPaypalOrderVersion objects filtered by the planified_description column
 * @method     array findByPlanifiedFrequency(string $planified_frequency) Return ChildPaypalOrderVersion objects filtered by the planified_frequency column
 * @method     array findByPlanifiedFrequencyInterval(int $planified_frequency_interval) Return ChildPaypalOrderVersion objects filtered by the planified_frequency_interval column
 * @method     array findByPlanifiedCycle(int $planified_cycle) Return ChildPaypalOrderVersion objects filtered by the planified_cycle column
 * @method     array findByPlanifiedActualCycle(int $planified_actual_cycle) Return ChildPaypalOrderVersion objects filtered by the planified_actual_cycle column
 * @method     array findByPlanifiedMinAmount(string $planified_min_amount) Return ChildPaypalOrderVersion objects filtered by the planified_min_amount column
 * @method     array findByPlanifiedMaxAmount(string $planified_max_amount) Return ChildPaypalOrderVersion objects filtered by the planified_max_amount column
 * @method     array findByCreatedAt(string $created_at) Return ChildPaypalOrderVersion objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildPaypalOrderVersion objects filtered by the updated_at column
 * @method     array findByVersion(int $version) Return ChildPaypalOrderVersion objects filtered by the version column
 * @method     array findByVersionCreatedAt(string $version_created_at) Return ChildPaypalOrderVersion objects filtered by the version_created_at column
 * @method     array findByVersionCreatedBy(string $version_created_by) Return ChildPaypalOrderVersion objects filtered by the version_created_by column
 * @method     array findByIdVersion(int $id_version) Return ChildPaypalOrderVersion objects filtered by the id_version column
 *
 */
abstract class PaypalOrderVersionQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \PayPal\Model\Base\PaypalOrderVersionQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\PayPal\\Model\\PaypalOrderVersion', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPaypalOrderVersionQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPaypalOrderVersionQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \PayPal\Model\PaypalOrderVersionQuery) {
            return $criteria;
        }
        $query = new \PayPal\Model\PaypalOrderVersionQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$id, $version] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildPaypalOrderVersion|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaypalOrderVersionTableMap::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1]))))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalOrderVersionTableMap::DATABASE_NAME);
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
     * @return   ChildPaypalOrderVersion A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PAYMENT_ID, AGREEMENT_ID, CREDIT_CARD_ID, STATE, AMOUNT, DESCRIPTION, PAYER_ID, TOKEN, PLANIFIED_TITLE, PLANIFIED_DESCRIPTION, PLANIFIED_FREQUENCY, PLANIFIED_FREQUENCY_INTERVAL, PLANIFIED_CYCLE, PLANIFIED_ACTUAL_CYCLE, PLANIFIED_MIN_AMOUNT, PLANIFIED_MAX_AMOUNT, CREATED_AT, UPDATED_AT, VERSION, VERSION_CREATED_AT, VERSION_CREATED_BY, ID_VERSION FROM paypal_order_version WHERE ID = :p0 AND VERSION = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildPaypalOrderVersion();
            $obj->hydrate($row);
            PaypalOrderVersionTableMap::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1])));
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
     * @return ChildPaypalOrderVersion|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(PaypalOrderVersionTableMap::ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(PaypalOrderVersionTableMap::ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(PaypalOrderVersionTableMap::VERSION, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
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
     * @see       filterByPaypalOrder()
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::ID, $id, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PAYMENT_ID, $paymentId, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::AGREEMENT_ID, $agreementId, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::CREDIT_CARD_ID, $creditCardId, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::STATE, $state, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByAmount($amount = null, $comparison = null)
    {
        if (is_array($amount)) {
            $useMinMax = false;
            if (isset($amount['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::AMOUNT, $amount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($amount['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::AMOUNT, $amount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::AMOUNT, $amount, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::DESCRIPTION, $description, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PAYER_ID, $payerId, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::TOKEN, $token, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_TITLE, $planifiedTitle, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_DESCRIPTION, $planifiedDescription, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_FREQUENCY, $planifiedFrequency, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPlanifiedFrequencyInterval($planifiedFrequencyInterval = null, $comparison = null)
    {
        if (is_array($planifiedFrequencyInterval)) {
            $useMinMax = false;
            if (isset($planifiedFrequencyInterval['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedFrequencyInterval['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_FREQUENCY_INTERVAL, $planifiedFrequencyInterval, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPlanifiedCycle($planifiedCycle = null, $comparison = null)
    {
        if (is_array($planifiedCycle)) {
            $useMinMax = false;
            if (isset($planifiedCycle['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_CYCLE, $planifiedCycle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedCycle['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_CYCLE, $planifiedCycle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_CYCLE, $planifiedCycle, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPlanifiedActualCycle($planifiedActualCycle = null, $comparison = null)
    {
        if (is_array($planifiedActualCycle)) {
            $useMinMax = false;
            if (isset($planifiedActualCycle['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedActualCycle['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_ACTUAL_CYCLE, $planifiedActualCycle, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPlanifiedMinAmount($planifiedMinAmount = null, $comparison = null)
    {
        if (is_array($planifiedMinAmount)) {
            $useMinMax = false;
            if (isset($planifiedMinAmount['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedMinAmount['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MIN_AMOUNT, $planifiedMinAmount, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPlanifiedMaxAmount($planifiedMaxAmount = null, $comparison = null)
    {
        if (is_array($planifiedMaxAmount)) {
            $useMinMax = false;
            if (isset($planifiedMaxAmount['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($planifiedMaxAmount['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::PLANIFIED_MAX_AMOUNT, $planifiedMaxAmount, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::CREATED_AT, $createdAt, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::UPDATED_AT, $updatedAt, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByVersion($version = null, $comparison = null)
    {
        if (is_array($version)) {
            $useMinMax = false;
            if (isset($version['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION, $version['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($version['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION, $version['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION, $version, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByVersionCreatedAt($versionCreatedAt = null, $comparison = null)
    {
        if (is_array($versionCreatedAt)) {
            $useMinMax = false;
            if (isset($versionCreatedAt['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION_CREATED_AT, $versionCreatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($versionCreatedAt['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION_CREATED_AT, $versionCreatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION_CREATED_AT, $versionCreatedAt, $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalOrderVersionTableMap::VERSION_CREATED_BY, $versionCreatedBy, $comparison);
    }

    /**
     * Filter the query on the id_version column
     *
     * Example usage:
     * <code>
     * $query->filterByIdVersion(1234); // WHERE id_version = 1234
     * $query->filterByIdVersion(array(12, 34)); // WHERE id_version IN (12, 34)
     * $query->filterByIdVersion(array('min' => 12)); // WHERE id_version > 12
     * </code>
     *
     * @param     mixed $idVersion The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByIdVersion($idVersion = null, $comparison = null)
    {
        if (is_array($idVersion)) {
            $useMinMax = false;
            if (isset($idVersion['min'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::ID_VERSION, $idVersion['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idVersion['max'])) {
                $this->addUsingAlias(PaypalOrderVersionTableMap::ID_VERSION, $idVersion['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalOrderVersionTableMap::ID_VERSION, $idVersion, $comparison);
    }

    /**
     * Filter the query by a related \PayPal\Model\PaypalOrder object
     *
     * @param \PayPal\Model\PaypalOrder|ObjectCollection $paypalOrder The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function filterByPaypalOrder($paypalOrder, $comparison = null)
    {
        if ($paypalOrder instanceof \PayPal\Model\PaypalOrder) {
            return $this
                ->addUsingAlias(PaypalOrderVersionTableMap::ID, $paypalOrder->getId(), $comparison);
        } elseif ($paypalOrder instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalOrderVersionTableMap::ID, $paypalOrder->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
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
     * @param   ChildPaypalOrderVersion $paypalOrderVersion Object to remove from the list of results
     *
     * @return ChildPaypalOrderVersionQuery The current query, for fluid interface
     */
    public function prune($paypalOrderVersion = null)
    {
        if ($paypalOrderVersion) {
            $this->addCond('pruneCond0', $this->getAliasedColName(PaypalOrderVersionTableMap::ID), $paypalOrderVersion->getId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(PaypalOrderVersionTableMap::VERSION), $paypalOrderVersion->getVersion(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the paypal_order_version table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderVersionTableMap::DATABASE_NAME);
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
            PaypalOrderVersionTableMap::clearInstancePool();
            PaypalOrderVersionTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildPaypalOrderVersion or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildPaypalOrderVersion object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderVersionTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PaypalOrderVersionTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        PaypalOrderVersionTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PaypalOrderVersionTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // PaypalOrderVersionQuery
