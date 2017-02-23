<?php

namespace PayPal\Model\Base;

use \Exception;
use \PDO;
use PayPal\Model\PaypalCustomer as ChildPaypalCustomer;
use PayPal\Model\PaypalCustomerQuery as ChildPaypalCustomerQuery;
use PayPal\Model\Map\PaypalCustomerTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Customer;

/**
 * Base class that represents a query for the 'paypal_customer' table.
 *
 *
 *
 * @method     ChildPaypalCustomerQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPaypalCustomerQuery orderByPaypalUserId($order = Criteria::ASC) Order by the paypal_user_id column
 * @method     ChildPaypalCustomerQuery orderByCreditCardId($order = Criteria::ASC) Order by the credit_card_id column
 * @method     ChildPaypalCustomerQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildPaypalCustomerQuery orderByGivenName($order = Criteria::ASC) Order by the given_name column
 * @method     ChildPaypalCustomerQuery orderByFamilyName($order = Criteria::ASC) Order by the family_name column
 * @method     ChildPaypalCustomerQuery orderByMiddleName($order = Criteria::ASC) Order by the middle_name column
 * @method     ChildPaypalCustomerQuery orderByPicture($order = Criteria::ASC) Order by the picture column
 * @method     ChildPaypalCustomerQuery orderByEmailVerified($order = Criteria::ASC) Order by the email_verified column
 * @method     ChildPaypalCustomerQuery orderByGender($order = Criteria::ASC) Order by the gender column
 * @method     ChildPaypalCustomerQuery orderByBirthday($order = Criteria::ASC) Order by the birthday column
 * @method     ChildPaypalCustomerQuery orderByZoneinfo($order = Criteria::ASC) Order by the zoneinfo column
 * @method     ChildPaypalCustomerQuery orderByLocale($order = Criteria::ASC) Order by the locale column
 * @method     ChildPaypalCustomerQuery orderByLanguage($order = Criteria::ASC) Order by the language column
 * @method     ChildPaypalCustomerQuery orderByVerified($order = Criteria::ASC) Order by the verified column
 * @method     ChildPaypalCustomerQuery orderByPhoneNumber($order = Criteria::ASC) Order by the phone_number column
 * @method     ChildPaypalCustomerQuery orderByVerifiedAccount($order = Criteria::ASC) Order by the verified_account column
 * @method     ChildPaypalCustomerQuery orderByAccountType($order = Criteria::ASC) Order by the account_type column
 * @method     ChildPaypalCustomerQuery orderByAgeRange($order = Criteria::ASC) Order by the age_range column
 * @method     ChildPaypalCustomerQuery orderByPayerId($order = Criteria::ASC) Order by the payer_id column
 * @method     ChildPaypalCustomerQuery orderByPostalCode($order = Criteria::ASC) Order by the postal_code column
 * @method     ChildPaypalCustomerQuery orderByLocality($order = Criteria::ASC) Order by the locality column
 * @method     ChildPaypalCustomerQuery orderByRegion($order = Criteria::ASC) Order by the region column
 * @method     ChildPaypalCustomerQuery orderByCountry($order = Criteria::ASC) Order by the country column
 * @method     ChildPaypalCustomerQuery orderByStreetAddress($order = Criteria::ASC) Order by the street_address column
 * @method     ChildPaypalCustomerQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildPaypalCustomerQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildPaypalCustomerQuery groupById() Group by the id column
 * @method     ChildPaypalCustomerQuery groupByPaypalUserId() Group by the paypal_user_id column
 * @method     ChildPaypalCustomerQuery groupByCreditCardId() Group by the credit_card_id column
 * @method     ChildPaypalCustomerQuery groupByName() Group by the name column
 * @method     ChildPaypalCustomerQuery groupByGivenName() Group by the given_name column
 * @method     ChildPaypalCustomerQuery groupByFamilyName() Group by the family_name column
 * @method     ChildPaypalCustomerQuery groupByMiddleName() Group by the middle_name column
 * @method     ChildPaypalCustomerQuery groupByPicture() Group by the picture column
 * @method     ChildPaypalCustomerQuery groupByEmailVerified() Group by the email_verified column
 * @method     ChildPaypalCustomerQuery groupByGender() Group by the gender column
 * @method     ChildPaypalCustomerQuery groupByBirthday() Group by the birthday column
 * @method     ChildPaypalCustomerQuery groupByZoneinfo() Group by the zoneinfo column
 * @method     ChildPaypalCustomerQuery groupByLocale() Group by the locale column
 * @method     ChildPaypalCustomerQuery groupByLanguage() Group by the language column
 * @method     ChildPaypalCustomerQuery groupByVerified() Group by the verified column
 * @method     ChildPaypalCustomerQuery groupByPhoneNumber() Group by the phone_number column
 * @method     ChildPaypalCustomerQuery groupByVerifiedAccount() Group by the verified_account column
 * @method     ChildPaypalCustomerQuery groupByAccountType() Group by the account_type column
 * @method     ChildPaypalCustomerQuery groupByAgeRange() Group by the age_range column
 * @method     ChildPaypalCustomerQuery groupByPayerId() Group by the payer_id column
 * @method     ChildPaypalCustomerQuery groupByPostalCode() Group by the postal_code column
 * @method     ChildPaypalCustomerQuery groupByLocality() Group by the locality column
 * @method     ChildPaypalCustomerQuery groupByRegion() Group by the region column
 * @method     ChildPaypalCustomerQuery groupByCountry() Group by the country column
 * @method     ChildPaypalCustomerQuery groupByStreetAddress() Group by the street_address column
 * @method     ChildPaypalCustomerQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildPaypalCustomerQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildPaypalCustomerQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPaypalCustomerQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPaypalCustomerQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPaypalCustomerQuery leftJoinCustomer($relationAlias = null) Adds a LEFT JOIN clause to the query using the Customer relation
 * @method     ChildPaypalCustomerQuery rightJoinCustomer($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Customer relation
 * @method     ChildPaypalCustomerQuery innerJoinCustomer($relationAlias = null) Adds a INNER JOIN clause to the query using the Customer relation
 *
 * @method     ChildPaypalCustomer findOne(ConnectionInterface $con = null) Return the first ChildPaypalCustomer matching the query
 * @method     ChildPaypalCustomer findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPaypalCustomer matching the query, or a new ChildPaypalCustomer object populated from the query conditions when no match is found
 *
 * @method     ChildPaypalCustomer findOneById(int $id) Return the first ChildPaypalCustomer filtered by the id column
 * @method     ChildPaypalCustomer findOneByPaypalUserId(int $paypal_user_id) Return the first ChildPaypalCustomer filtered by the paypal_user_id column
 * @method     ChildPaypalCustomer findOneByCreditCardId(string $credit_card_id) Return the first ChildPaypalCustomer filtered by the credit_card_id column
 * @method     ChildPaypalCustomer findOneByName(string $name) Return the first ChildPaypalCustomer filtered by the name column
 * @method     ChildPaypalCustomer findOneByGivenName(string $given_name) Return the first ChildPaypalCustomer filtered by the given_name column
 * @method     ChildPaypalCustomer findOneByFamilyName(string $family_name) Return the first ChildPaypalCustomer filtered by the family_name column
 * @method     ChildPaypalCustomer findOneByMiddleName(string $middle_name) Return the first ChildPaypalCustomer filtered by the middle_name column
 * @method     ChildPaypalCustomer findOneByPicture(string $picture) Return the first ChildPaypalCustomer filtered by the picture column
 * @method     ChildPaypalCustomer findOneByEmailVerified(int $email_verified) Return the first ChildPaypalCustomer filtered by the email_verified column
 * @method     ChildPaypalCustomer findOneByGender(string $gender) Return the first ChildPaypalCustomer filtered by the gender column
 * @method     ChildPaypalCustomer findOneByBirthday(string $birthday) Return the first ChildPaypalCustomer filtered by the birthday column
 * @method     ChildPaypalCustomer findOneByZoneinfo(string $zoneinfo) Return the first ChildPaypalCustomer filtered by the zoneinfo column
 * @method     ChildPaypalCustomer findOneByLocale(string $locale) Return the first ChildPaypalCustomer filtered by the locale column
 * @method     ChildPaypalCustomer findOneByLanguage(string $language) Return the first ChildPaypalCustomer filtered by the language column
 * @method     ChildPaypalCustomer findOneByVerified(int $verified) Return the first ChildPaypalCustomer filtered by the verified column
 * @method     ChildPaypalCustomer findOneByPhoneNumber(string $phone_number) Return the first ChildPaypalCustomer filtered by the phone_number column
 * @method     ChildPaypalCustomer findOneByVerifiedAccount(string $verified_account) Return the first ChildPaypalCustomer filtered by the verified_account column
 * @method     ChildPaypalCustomer findOneByAccountType(string $account_type) Return the first ChildPaypalCustomer filtered by the account_type column
 * @method     ChildPaypalCustomer findOneByAgeRange(string $age_range) Return the first ChildPaypalCustomer filtered by the age_range column
 * @method     ChildPaypalCustomer findOneByPayerId(string $payer_id) Return the first ChildPaypalCustomer filtered by the payer_id column
 * @method     ChildPaypalCustomer findOneByPostalCode(string $postal_code) Return the first ChildPaypalCustomer filtered by the postal_code column
 * @method     ChildPaypalCustomer findOneByLocality(string $locality) Return the first ChildPaypalCustomer filtered by the locality column
 * @method     ChildPaypalCustomer findOneByRegion(string $region) Return the first ChildPaypalCustomer filtered by the region column
 * @method     ChildPaypalCustomer findOneByCountry(string $country) Return the first ChildPaypalCustomer filtered by the country column
 * @method     ChildPaypalCustomer findOneByStreetAddress(string $street_address) Return the first ChildPaypalCustomer filtered by the street_address column
 * @method     ChildPaypalCustomer findOneByCreatedAt(string $created_at) Return the first ChildPaypalCustomer filtered by the created_at column
 * @method     ChildPaypalCustomer findOneByUpdatedAt(string $updated_at) Return the first ChildPaypalCustomer filtered by the updated_at column
 *
 * @method     array findById(int $id) Return ChildPaypalCustomer objects filtered by the id column
 * @method     array findByPaypalUserId(int $paypal_user_id) Return ChildPaypalCustomer objects filtered by the paypal_user_id column
 * @method     array findByCreditCardId(string $credit_card_id) Return ChildPaypalCustomer objects filtered by the credit_card_id column
 * @method     array findByName(string $name) Return ChildPaypalCustomer objects filtered by the name column
 * @method     array findByGivenName(string $given_name) Return ChildPaypalCustomer objects filtered by the given_name column
 * @method     array findByFamilyName(string $family_name) Return ChildPaypalCustomer objects filtered by the family_name column
 * @method     array findByMiddleName(string $middle_name) Return ChildPaypalCustomer objects filtered by the middle_name column
 * @method     array findByPicture(string $picture) Return ChildPaypalCustomer objects filtered by the picture column
 * @method     array findByEmailVerified(int $email_verified) Return ChildPaypalCustomer objects filtered by the email_verified column
 * @method     array findByGender(string $gender) Return ChildPaypalCustomer objects filtered by the gender column
 * @method     array findByBirthday(string $birthday) Return ChildPaypalCustomer objects filtered by the birthday column
 * @method     array findByZoneinfo(string $zoneinfo) Return ChildPaypalCustomer objects filtered by the zoneinfo column
 * @method     array findByLocale(string $locale) Return ChildPaypalCustomer objects filtered by the locale column
 * @method     array findByLanguage(string $language) Return ChildPaypalCustomer objects filtered by the language column
 * @method     array findByVerified(int $verified) Return ChildPaypalCustomer objects filtered by the verified column
 * @method     array findByPhoneNumber(string $phone_number) Return ChildPaypalCustomer objects filtered by the phone_number column
 * @method     array findByVerifiedAccount(string $verified_account) Return ChildPaypalCustomer objects filtered by the verified_account column
 * @method     array findByAccountType(string $account_type) Return ChildPaypalCustomer objects filtered by the account_type column
 * @method     array findByAgeRange(string $age_range) Return ChildPaypalCustomer objects filtered by the age_range column
 * @method     array findByPayerId(string $payer_id) Return ChildPaypalCustomer objects filtered by the payer_id column
 * @method     array findByPostalCode(string $postal_code) Return ChildPaypalCustomer objects filtered by the postal_code column
 * @method     array findByLocality(string $locality) Return ChildPaypalCustomer objects filtered by the locality column
 * @method     array findByRegion(string $region) Return ChildPaypalCustomer objects filtered by the region column
 * @method     array findByCountry(string $country) Return ChildPaypalCustomer objects filtered by the country column
 * @method     array findByStreetAddress(string $street_address) Return ChildPaypalCustomer objects filtered by the street_address column
 * @method     array findByCreatedAt(string $created_at) Return ChildPaypalCustomer objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildPaypalCustomer objects filtered by the updated_at column
 *
 */
abstract class PaypalCustomerQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \PayPal\Model\Base\PaypalCustomerQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\PayPal\\Model\\PaypalCustomer', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPaypalCustomerQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPaypalCustomerQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \PayPal\Model\PaypalCustomerQuery) {
            return $criteria;
        }
        $query = new \PayPal\Model\PaypalCustomerQuery();
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
     * @param array[$id, $paypal_user_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildPaypalCustomer|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaypalCustomerTableMap::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1]))))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalCustomerTableMap::DATABASE_NAME);
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
     * @return   ChildPaypalCustomer A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PAYPAL_USER_ID, CREDIT_CARD_ID, NAME, GIVEN_NAME, FAMILY_NAME, MIDDLE_NAME, PICTURE, EMAIL_VERIFIED, GENDER, BIRTHDAY, ZONEINFO, LOCALE, LANGUAGE, VERIFIED, PHONE_NUMBER, VERIFIED_ACCOUNT, ACCOUNT_TYPE, AGE_RANGE, PAYER_ID, POSTAL_CODE, LOCALITY, REGION, COUNTRY, STREET_ADDRESS, CREATED_AT, UPDATED_AT FROM paypal_customer WHERE ID = :p0 AND PAYPAL_USER_ID = :p1';
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
            $obj = new ChildPaypalCustomer();
            $obj->hydrate($row);
            PaypalCustomerTableMap::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1])));
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
     * @return ChildPaypalCustomer|array|mixed the result, formatted by the current formatter
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
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(PaypalCustomerTableMap::ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(PaypalCustomerTableMap::PAYPAL_USER_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(PaypalCustomerTableMap::ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(PaypalCustomerTableMap::PAYPAL_USER_ID, $key[1], Criteria::EQUAL);
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
     * @see       filterByCustomer()
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the paypal_user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPaypalUserId(1234); // WHERE paypal_user_id = 1234
     * $query->filterByPaypalUserId(array(12, 34)); // WHERE paypal_user_id IN (12, 34)
     * $query->filterByPaypalUserId(array('min' => 12)); // WHERE paypal_user_id > 12
     * </code>
     *
     * @param     mixed $paypalUserId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPaypalUserId($paypalUserId = null, $comparison = null)
    {
        if (is_array($paypalUserId)) {
            $useMinMax = false;
            if (isset($paypalUserId['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::PAYPAL_USER_ID, $paypalUserId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($paypalUserId['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::PAYPAL_USER_ID, $paypalUserId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::PAYPAL_USER_ID, $paypalUserId, $comparison);
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
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalCustomerTableMap::CREDIT_CARD_ID, $creditCardId, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $name)) {
                $name = str_replace('*', '%', $name);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::NAME, $name, $comparison);
    }

    /**
     * Filter the query on the given_name column
     *
     * Example usage:
     * <code>
     * $query->filterByGivenName('fooValue');   // WHERE given_name = 'fooValue'
     * $query->filterByGivenName('%fooValue%'); // WHERE given_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $givenName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByGivenName($givenName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($givenName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $givenName)) {
                $givenName = str_replace('*', '%', $givenName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::GIVEN_NAME, $givenName, $comparison);
    }

    /**
     * Filter the query on the family_name column
     *
     * Example usage:
     * <code>
     * $query->filterByFamilyName('fooValue');   // WHERE family_name = 'fooValue'
     * $query->filterByFamilyName('%fooValue%'); // WHERE family_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $familyName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByFamilyName($familyName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($familyName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $familyName)) {
                $familyName = str_replace('*', '%', $familyName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::FAMILY_NAME, $familyName, $comparison);
    }

    /**
     * Filter the query on the middle_name column
     *
     * Example usage:
     * <code>
     * $query->filterByMiddleName('fooValue');   // WHERE middle_name = 'fooValue'
     * $query->filterByMiddleName('%fooValue%'); // WHERE middle_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $middleName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByMiddleName($middleName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($middleName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $middleName)) {
                $middleName = str_replace('*', '%', $middleName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::MIDDLE_NAME, $middleName, $comparison);
    }

    /**
     * Filter the query on the picture column
     *
     * Example usage:
     * <code>
     * $query->filterByPicture('fooValue');   // WHERE picture = 'fooValue'
     * $query->filterByPicture('%fooValue%'); // WHERE picture LIKE '%fooValue%'
     * </code>
     *
     * @param     string $picture The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPicture($picture = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($picture)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $picture)) {
                $picture = str_replace('*', '%', $picture);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::PICTURE, $picture, $comparison);
    }

    /**
     * Filter the query on the email_verified column
     *
     * Example usage:
     * <code>
     * $query->filterByEmailVerified(1234); // WHERE email_verified = 1234
     * $query->filterByEmailVerified(array(12, 34)); // WHERE email_verified IN (12, 34)
     * $query->filterByEmailVerified(array('min' => 12)); // WHERE email_verified > 12
     * </code>
     *
     * @param     mixed $emailVerified The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByEmailVerified($emailVerified = null, $comparison = null)
    {
        if (is_array($emailVerified)) {
            $useMinMax = false;
            if (isset($emailVerified['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::EMAIL_VERIFIED, $emailVerified['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($emailVerified['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::EMAIL_VERIFIED, $emailVerified['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::EMAIL_VERIFIED, $emailVerified, $comparison);
    }

    /**
     * Filter the query on the gender column
     *
     * Example usage:
     * <code>
     * $query->filterByGender('fooValue');   // WHERE gender = 'fooValue'
     * $query->filterByGender('%fooValue%'); // WHERE gender LIKE '%fooValue%'
     * </code>
     *
     * @param     string $gender The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByGender($gender = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($gender)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $gender)) {
                $gender = str_replace('*', '%', $gender);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::GENDER, $gender, $comparison);
    }

    /**
     * Filter the query on the birthday column
     *
     * Example usage:
     * <code>
     * $query->filterByBirthday('fooValue');   // WHERE birthday = 'fooValue'
     * $query->filterByBirthday('%fooValue%'); // WHERE birthday LIKE '%fooValue%'
     * </code>
     *
     * @param     string $birthday The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByBirthday($birthday = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($birthday)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $birthday)) {
                $birthday = str_replace('*', '%', $birthday);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::BIRTHDAY, $birthday, $comparison);
    }

    /**
     * Filter the query on the zoneinfo column
     *
     * Example usage:
     * <code>
     * $query->filterByZoneinfo('fooValue');   // WHERE zoneinfo = 'fooValue'
     * $query->filterByZoneinfo('%fooValue%'); // WHERE zoneinfo LIKE '%fooValue%'
     * </code>
     *
     * @param     string $zoneinfo The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByZoneinfo($zoneinfo = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($zoneinfo)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $zoneinfo)) {
                $zoneinfo = str_replace('*', '%', $zoneinfo);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::ZONEINFO, $zoneinfo, $comparison);
    }

    /**
     * Filter the query on the locale column
     *
     * Example usage:
     * <code>
     * $query->filterByLocale('fooValue');   // WHERE locale = 'fooValue'
     * $query->filterByLocale('%fooValue%'); // WHERE locale LIKE '%fooValue%'
     * </code>
     *
     * @param     string $locale The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByLocale($locale = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($locale)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $locale)) {
                $locale = str_replace('*', '%', $locale);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::LOCALE, $locale, $comparison);
    }

    /**
     * Filter the query on the language column
     *
     * Example usage:
     * <code>
     * $query->filterByLanguage('fooValue');   // WHERE language = 'fooValue'
     * $query->filterByLanguage('%fooValue%'); // WHERE language LIKE '%fooValue%'
     * </code>
     *
     * @param     string $language The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByLanguage($language = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($language)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $language)) {
                $language = str_replace('*', '%', $language);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::LANGUAGE, $language, $comparison);
    }

    /**
     * Filter the query on the verified column
     *
     * Example usage:
     * <code>
     * $query->filterByVerified(1234); // WHERE verified = 1234
     * $query->filterByVerified(array(12, 34)); // WHERE verified IN (12, 34)
     * $query->filterByVerified(array('min' => 12)); // WHERE verified > 12
     * </code>
     *
     * @param     mixed $verified The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByVerified($verified = null, $comparison = null)
    {
        if (is_array($verified)) {
            $useMinMax = false;
            if (isset($verified['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::VERIFIED, $verified['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($verified['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::VERIFIED, $verified['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::VERIFIED, $verified, $comparison);
    }

    /**
     * Filter the query on the phone_number column
     *
     * Example usage:
     * <code>
     * $query->filterByPhoneNumber('fooValue');   // WHERE phone_number = 'fooValue'
     * $query->filterByPhoneNumber('%fooValue%'); // WHERE phone_number LIKE '%fooValue%'
     * </code>
     *
     * @param     string $phoneNumber The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPhoneNumber($phoneNumber = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($phoneNumber)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $phoneNumber)) {
                $phoneNumber = str_replace('*', '%', $phoneNumber);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::PHONE_NUMBER, $phoneNumber, $comparison);
    }

    /**
     * Filter the query on the verified_account column
     *
     * Example usage:
     * <code>
     * $query->filterByVerifiedAccount('fooValue');   // WHERE verified_account = 'fooValue'
     * $query->filterByVerifiedAccount('%fooValue%'); // WHERE verified_account LIKE '%fooValue%'
     * </code>
     *
     * @param     string $verifiedAccount The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByVerifiedAccount($verifiedAccount = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($verifiedAccount)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $verifiedAccount)) {
                $verifiedAccount = str_replace('*', '%', $verifiedAccount);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::VERIFIED_ACCOUNT, $verifiedAccount, $comparison);
    }

    /**
     * Filter the query on the account_type column
     *
     * Example usage:
     * <code>
     * $query->filterByAccountType('fooValue');   // WHERE account_type = 'fooValue'
     * $query->filterByAccountType('%fooValue%'); // WHERE account_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $accountType The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByAccountType($accountType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($accountType)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $accountType)) {
                $accountType = str_replace('*', '%', $accountType);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::ACCOUNT_TYPE, $accountType, $comparison);
    }

    /**
     * Filter the query on the age_range column
     *
     * Example usage:
     * <code>
     * $query->filterByAgeRange('fooValue');   // WHERE age_range = 'fooValue'
     * $query->filterByAgeRange('%fooValue%'); // WHERE age_range LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ageRange The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByAgeRange($ageRange = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ageRange)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $ageRange)) {
                $ageRange = str_replace('*', '%', $ageRange);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::AGE_RANGE, $ageRange, $comparison);
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
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PaypalCustomerTableMap::PAYER_ID, $payerId, $comparison);
    }

    /**
     * Filter the query on the postal_code column
     *
     * Example usage:
     * <code>
     * $query->filterByPostalCode('fooValue');   // WHERE postal_code = 'fooValue'
     * $query->filterByPostalCode('%fooValue%'); // WHERE postal_code LIKE '%fooValue%'
     * </code>
     *
     * @param     string $postalCode The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByPostalCode($postalCode = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($postalCode)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $postalCode)) {
                $postalCode = str_replace('*', '%', $postalCode);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::POSTAL_CODE, $postalCode, $comparison);
    }

    /**
     * Filter the query on the locality column
     *
     * Example usage:
     * <code>
     * $query->filterByLocality('fooValue');   // WHERE locality = 'fooValue'
     * $query->filterByLocality('%fooValue%'); // WHERE locality LIKE '%fooValue%'
     * </code>
     *
     * @param     string $locality The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByLocality($locality = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($locality)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $locality)) {
                $locality = str_replace('*', '%', $locality);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::LOCALITY, $locality, $comparison);
    }

    /**
     * Filter the query on the region column
     *
     * Example usage:
     * <code>
     * $query->filterByRegion('fooValue');   // WHERE region = 'fooValue'
     * $query->filterByRegion('%fooValue%'); // WHERE region LIKE '%fooValue%'
     * </code>
     *
     * @param     string $region The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByRegion($region = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($region)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $region)) {
                $region = str_replace('*', '%', $region);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::REGION, $region, $comparison);
    }

    /**
     * Filter the query on the country column
     *
     * Example usage:
     * <code>
     * $query->filterByCountry('fooValue');   // WHERE country = 'fooValue'
     * $query->filterByCountry('%fooValue%'); // WHERE country LIKE '%fooValue%'
     * </code>
     *
     * @param     string $country The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByCountry($country = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($country)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $country)) {
                $country = str_replace('*', '%', $country);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::COUNTRY, $country, $comparison);
    }

    /**
     * Filter the query on the street_address column
     *
     * Example usage:
     * <code>
     * $query->filterByStreetAddress('fooValue');   // WHERE street_address = 'fooValue'
     * $query->filterByStreetAddress('%fooValue%'); // WHERE street_address LIKE '%fooValue%'
     * </code>
     *
     * @param     string $streetAddress The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByStreetAddress($streetAddress = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($streetAddress)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $streetAddress)) {
                $streetAddress = str_replace('*', '%', $streetAddress);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::STREET_ADDRESS, $streetAddress, $comparison);
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
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::CREATED_AT, $createdAt, $comparison);
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
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(PaypalCustomerTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PaypalCustomerTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\Customer object
     *
     * @param \Thelia\Model\Customer|ObjectCollection $customer The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function filterByCustomer($customer, $comparison = null)
    {
        if ($customer instanceof \Thelia\Model\Customer) {
            return $this
                ->addUsingAlias(PaypalCustomerTableMap::ID, $customer->getId(), $comparison);
        } elseif ($customer instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PaypalCustomerTableMap::ID, $customer->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCustomer() only accepts arguments of type \Thelia\Model\Customer or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Customer relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function joinCustomer($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Customer');

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
            $this->addJoinObject($join, 'Customer');
        }

        return $this;
    }

    /**
     * Use the Customer relation Customer object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\CustomerQuery A secondary query class using the current class as primary query
     */
    public function useCustomerQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCustomer($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Customer', '\Thelia\Model\CustomerQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPaypalCustomer $paypalCustomer Object to remove from the list of results
     *
     * @return ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function prune($paypalCustomer = null)
    {
        if ($paypalCustomer) {
            $this->addCond('pruneCond0', $this->getAliasedColName(PaypalCustomerTableMap::ID), $paypalCustomer->getId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(PaypalCustomerTableMap::PAYPAL_USER_ID), $paypalCustomer->getPaypalUserId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the paypal_customer table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
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
            PaypalCustomerTableMap::clearInstancePool();
            PaypalCustomerTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildPaypalCustomer or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildPaypalCustomer object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PaypalCustomerTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        PaypalCustomerTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PaypalCustomerTableMap::clearRelatedInstancePool();
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
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalCustomerTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(PaypalCustomerTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalCustomerTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalCustomerTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(PaypalCustomerTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildPaypalCustomerQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(PaypalCustomerTableMap::CREATED_AT);
    }

} // PaypalCustomerQuery
