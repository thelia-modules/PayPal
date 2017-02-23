<?php

namespace PayPal\Model\Map;

use PayPal\Model\PaypalCustomer;
use PayPal\Model\PaypalCustomerQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'paypal_customer' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class PaypalCustomerTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'PayPal.Model.Map.PaypalCustomerTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'paypal_customer';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\PayPal\\Model\\PaypalCustomer';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'PayPal.Model.PaypalCustomer';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 27;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 27;

    /**
     * the column name for the ID field
     */
    const ID = 'paypal_customer.ID';

    /**
     * the column name for the PAYPAL_USER_ID field
     */
    const PAYPAL_USER_ID = 'paypal_customer.PAYPAL_USER_ID';

    /**
     * the column name for the CREDIT_CARD_ID field
     */
    const CREDIT_CARD_ID = 'paypal_customer.CREDIT_CARD_ID';

    /**
     * the column name for the NAME field
     */
    const NAME = 'paypal_customer.NAME';

    /**
     * the column name for the GIVEN_NAME field
     */
    const GIVEN_NAME = 'paypal_customer.GIVEN_NAME';

    /**
     * the column name for the FAMILY_NAME field
     */
    const FAMILY_NAME = 'paypal_customer.FAMILY_NAME';

    /**
     * the column name for the MIDDLE_NAME field
     */
    const MIDDLE_NAME = 'paypal_customer.MIDDLE_NAME';

    /**
     * the column name for the PICTURE field
     */
    const PICTURE = 'paypal_customer.PICTURE';

    /**
     * the column name for the EMAIL_VERIFIED field
     */
    const EMAIL_VERIFIED = 'paypal_customer.EMAIL_VERIFIED';

    /**
     * the column name for the GENDER field
     */
    const GENDER = 'paypal_customer.GENDER';

    /**
     * the column name for the BIRTHDAY field
     */
    const BIRTHDAY = 'paypal_customer.BIRTHDAY';

    /**
     * the column name for the ZONEINFO field
     */
    const ZONEINFO = 'paypal_customer.ZONEINFO';

    /**
     * the column name for the LOCALE field
     */
    const LOCALE = 'paypal_customer.LOCALE';

    /**
     * the column name for the LANGUAGE field
     */
    const LANGUAGE = 'paypal_customer.LANGUAGE';

    /**
     * the column name for the VERIFIED field
     */
    const VERIFIED = 'paypal_customer.VERIFIED';

    /**
     * the column name for the PHONE_NUMBER field
     */
    const PHONE_NUMBER = 'paypal_customer.PHONE_NUMBER';

    /**
     * the column name for the VERIFIED_ACCOUNT field
     */
    const VERIFIED_ACCOUNT = 'paypal_customer.VERIFIED_ACCOUNT';

    /**
     * the column name for the ACCOUNT_TYPE field
     */
    const ACCOUNT_TYPE = 'paypal_customer.ACCOUNT_TYPE';

    /**
     * the column name for the AGE_RANGE field
     */
    const AGE_RANGE = 'paypal_customer.AGE_RANGE';

    /**
     * the column name for the PAYER_ID field
     */
    const PAYER_ID = 'paypal_customer.PAYER_ID';

    /**
     * the column name for the POSTAL_CODE field
     */
    const POSTAL_CODE = 'paypal_customer.POSTAL_CODE';

    /**
     * the column name for the LOCALITY field
     */
    const LOCALITY = 'paypal_customer.LOCALITY';

    /**
     * the column name for the REGION field
     */
    const REGION = 'paypal_customer.REGION';

    /**
     * the column name for the COUNTRY field
     */
    const COUNTRY = 'paypal_customer.COUNTRY';

    /**
     * the column name for the STREET_ADDRESS field
     */
    const STREET_ADDRESS = 'paypal_customer.STREET_ADDRESS';

    /**
     * the column name for the CREATED_AT field
     */
    const CREATED_AT = 'paypal_customer.CREATED_AT';

    /**
     * the column name for the UPDATED_AT field
     */
    const UPDATED_AT = 'paypal_customer.UPDATED_AT';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'PaypalUserId', 'CreditCardId', 'Name', 'GivenName', 'FamilyName', 'MiddleName', 'Picture', 'EmailVerified', 'Gender', 'Birthday', 'Zoneinfo', 'Locale', 'Language', 'Verified', 'PhoneNumber', 'VerifiedAccount', 'AccountType', 'AgeRange', 'PayerId', 'PostalCode', 'Locality', 'Region', 'Country', 'StreetAddress', 'CreatedAt', 'UpdatedAt', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'paypalUserId', 'creditCardId', 'name', 'givenName', 'familyName', 'middleName', 'picture', 'emailVerified', 'gender', 'birthday', 'zoneinfo', 'locale', 'language', 'verified', 'phoneNumber', 'verifiedAccount', 'accountType', 'ageRange', 'payerId', 'postalCode', 'locality', 'region', 'country', 'streetAddress', 'createdAt', 'updatedAt', ),
        self::TYPE_COLNAME       => array(PaypalCustomerTableMap::ID, PaypalCustomerTableMap::PAYPAL_USER_ID, PaypalCustomerTableMap::CREDIT_CARD_ID, PaypalCustomerTableMap::NAME, PaypalCustomerTableMap::GIVEN_NAME, PaypalCustomerTableMap::FAMILY_NAME, PaypalCustomerTableMap::MIDDLE_NAME, PaypalCustomerTableMap::PICTURE, PaypalCustomerTableMap::EMAIL_VERIFIED, PaypalCustomerTableMap::GENDER, PaypalCustomerTableMap::BIRTHDAY, PaypalCustomerTableMap::ZONEINFO, PaypalCustomerTableMap::LOCALE, PaypalCustomerTableMap::LANGUAGE, PaypalCustomerTableMap::VERIFIED, PaypalCustomerTableMap::PHONE_NUMBER, PaypalCustomerTableMap::VERIFIED_ACCOUNT, PaypalCustomerTableMap::ACCOUNT_TYPE, PaypalCustomerTableMap::AGE_RANGE, PaypalCustomerTableMap::PAYER_ID, PaypalCustomerTableMap::POSTAL_CODE, PaypalCustomerTableMap::LOCALITY, PaypalCustomerTableMap::REGION, PaypalCustomerTableMap::COUNTRY, PaypalCustomerTableMap::STREET_ADDRESS, PaypalCustomerTableMap::CREATED_AT, PaypalCustomerTableMap::UPDATED_AT, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'PAYPAL_USER_ID', 'CREDIT_CARD_ID', 'NAME', 'GIVEN_NAME', 'FAMILY_NAME', 'MIDDLE_NAME', 'PICTURE', 'EMAIL_VERIFIED', 'GENDER', 'BIRTHDAY', 'ZONEINFO', 'LOCALE', 'LANGUAGE', 'VERIFIED', 'PHONE_NUMBER', 'VERIFIED_ACCOUNT', 'ACCOUNT_TYPE', 'AGE_RANGE', 'PAYER_ID', 'POSTAL_CODE', 'LOCALITY', 'REGION', 'COUNTRY', 'STREET_ADDRESS', 'CREATED_AT', 'UPDATED_AT', ),
        self::TYPE_FIELDNAME     => array('id', 'paypal_user_id', 'credit_card_id', 'name', 'given_name', 'family_name', 'middle_name', 'picture', 'email_verified', 'gender', 'birthday', 'zoneinfo', 'locale', 'language', 'verified', 'phone_number', 'verified_account', 'account_type', 'age_range', 'payer_id', 'postal_code', 'locality', 'region', 'country', 'street_address', 'created_at', 'updated_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'PaypalUserId' => 1, 'CreditCardId' => 2, 'Name' => 3, 'GivenName' => 4, 'FamilyName' => 5, 'MiddleName' => 6, 'Picture' => 7, 'EmailVerified' => 8, 'Gender' => 9, 'Birthday' => 10, 'Zoneinfo' => 11, 'Locale' => 12, 'Language' => 13, 'Verified' => 14, 'PhoneNumber' => 15, 'VerifiedAccount' => 16, 'AccountType' => 17, 'AgeRange' => 18, 'PayerId' => 19, 'PostalCode' => 20, 'Locality' => 21, 'Region' => 22, 'Country' => 23, 'StreetAddress' => 24, 'CreatedAt' => 25, 'UpdatedAt' => 26, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'paypalUserId' => 1, 'creditCardId' => 2, 'name' => 3, 'givenName' => 4, 'familyName' => 5, 'middleName' => 6, 'picture' => 7, 'emailVerified' => 8, 'gender' => 9, 'birthday' => 10, 'zoneinfo' => 11, 'locale' => 12, 'language' => 13, 'verified' => 14, 'phoneNumber' => 15, 'verifiedAccount' => 16, 'accountType' => 17, 'ageRange' => 18, 'payerId' => 19, 'postalCode' => 20, 'locality' => 21, 'region' => 22, 'country' => 23, 'streetAddress' => 24, 'createdAt' => 25, 'updatedAt' => 26, ),
        self::TYPE_COLNAME       => array(PaypalCustomerTableMap::ID => 0, PaypalCustomerTableMap::PAYPAL_USER_ID => 1, PaypalCustomerTableMap::CREDIT_CARD_ID => 2, PaypalCustomerTableMap::NAME => 3, PaypalCustomerTableMap::GIVEN_NAME => 4, PaypalCustomerTableMap::FAMILY_NAME => 5, PaypalCustomerTableMap::MIDDLE_NAME => 6, PaypalCustomerTableMap::PICTURE => 7, PaypalCustomerTableMap::EMAIL_VERIFIED => 8, PaypalCustomerTableMap::GENDER => 9, PaypalCustomerTableMap::BIRTHDAY => 10, PaypalCustomerTableMap::ZONEINFO => 11, PaypalCustomerTableMap::LOCALE => 12, PaypalCustomerTableMap::LANGUAGE => 13, PaypalCustomerTableMap::VERIFIED => 14, PaypalCustomerTableMap::PHONE_NUMBER => 15, PaypalCustomerTableMap::VERIFIED_ACCOUNT => 16, PaypalCustomerTableMap::ACCOUNT_TYPE => 17, PaypalCustomerTableMap::AGE_RANGE => 18, PaypalCustomerTableMap::PAYER_ID => 19, PaypalCustomerTableMap::POSTAL_CODE => 20, PaypalCustomerTableMap::LOCALITY => 21, PaypalCustomerTableMap::REGION => 22, PaypalCustomerTableMap::COUNTRY => 23, PaypalCustomerTableMap::STREET_ADDRESS => 24, PaypalCustomerTableMap::CREATED_AT => 25, PaypalCustomerTableMap::UPDATED_AT => 26, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'PAYPAL_USER_ID' => 1, 'CREDIT_CARD_ID' => 2, 'NAME' => 3, 'GIVEN_NAME' => 4, 'FAMILY_NAME' => 5, 'MIDDLE_NAME' => 6, 'PICTURE' => 7, 'EMAIL_VERIFIED' => 8, 'GENDER' => 9, 'BIRTHDAY' => 10, 'ZONEINFO' => 11, 'LOCALE' => 12, 'LANGUAGE' => 13, 'VERIFIED' => 14, 'PHONE_NUMBER' => 15, 'VERIFIED_ACCOUNT' => 16, 'ACCOUNT_TYPE' => 17, 'AGE_RANGE' => 18, 'PAYER_ID' => 19, 'POSTAL_CODE' => 20, 'LOCALITY' => 21, 'REGION' => 22, 'COUNTRY' => 23, 'STREET_ADDRESS' => 24, 'CREATED_AT' => 25, 'UPDATED_AT' => 26, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'paypal_user_id' => 1, 'credit_card_id' => 2, 'name' => 3, 'given_name' => 4, 'family_name' => 5, 'middle_name' => 6, 'picture' => 7, 'email_verified' => 8, 'gender' => 9, 'birthday' => 10, 'zoneinfo' => 11, 'locale' => 12, 'language' => 13, 'verified' => 14, 'phone_number' => 15, 'verified_account' => 16, 'account_type' => 17, 'age_range' => 18, 'payer_id' => 19, 'postal_code' => 20, 'locality' => 21, 'region' => 22, 'country' => 23, 'street_address' => 24, 'created_at' => 25, 'updated_at' => 26, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('paypal_customer');
        $this->setPhpName('PaypalCustomer');
        $this->setClassName('\\PayPal\\Model\\PaypalCustomer');
        $this->setPackage('PayPal.Model');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('ID', 'Id', 'INTEGER' , 'customer', 'ID', true, null, null);
        $this->addPrimaryKey('PAYPAL_USER_ID', 'PaypalUserId', 'INTEGER', true, null, null);
        $this->addColumn('CREDIT_CARD_ID', 'CreditCardId', 'VARCHAR', false, 40, null);
        $this->addColumn('NAME', 'Name', 'VARCHAR', false, 255, null);
        $this->addColumn('GIVEN_NAME', 'GivenName', 'VARCHAR', false, 255, null);
        $this->addColumn('FAMILY_NAME', 'FamilyName', 'VARCHAR', false, 255, null);
        $this->addColumn('MIDDLE_NAME', 'MiddleName', 'VARCHAR', false, 255, null);
        $this->addColumn('PICTURE', 'Picture', 'VARCHAR', false, 255, null);
        $this->addColumn('EMAIL_VERIFIED', 'EmailVerified', 'TINYINT', false, null, null);
        $this->addColumn('GENDER', 'Gender', 'VARCHAR', false, 255, null);
        $this->addColumn('BIRTHDAY', 'Birthday', 'VARCHAR', false, 255, null);
        $this->addColumn('ZONEINFO', 'Zoneinfo', 'VARCHAR', false, 255, null);
        $this->addColumn('LOCALE', 'Locale', 'VARCHAR', false, 255, null);
        $this->addColumn('LANGUAGE', 'Language', 'VARCHAR', false, 255, null);
        $this->addColumn('VERIFIED', 'Verified', 'TINYINT', false, null, null);
        $this->addColumn('PHONE_NUMBER', 'PhoneNumber', 'VARCHAR', false, 255, null);
        $this->addColumn('VERIFIED_ACCOUNT', 'VerifiedAccount', 'VARCHAR', false, 255, null);
        $this->addColumn('ACCOUNT_TYPE', 'AccountType', 'VARCHAR', false, 255, null);
        $this->addColumn('AGE_RANGE', 'AgeRange', 'VARCHAR', false, 255, null);
        $this->addColumn('PAYER_ID', 'PayerId', 'VARCHAR', false, 255, null);
        $this->addColumn('POSTAL_CODE', 'PostalCode', 'VARCHAR', false, 255, null);
        $this->addColumn('LOCALITY', 'Locality', 'VARCHAR', false, 255, null);
        $this->addColumn('REGION', 'Region', 'VARCHAR', false, 255, null);
        $this->addColumn('COUNTRY', 'Country', 'VARCHAR', false, 255, null);
        $this->addColumn('STREET_ADDRESS', 'StreetAddress', 'VARCHAR', false, 255, null);
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Customer', '\\Thelia\\Model\\Customer', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', 'RESTRICT');
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', ),
        );
    } // getBehaviors()

    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database. In some cases you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by find*()
     * and findPk*() calls.
     *
     * @param \PayPal\Model\PaypalCustomer $obj A \PayPal\Model\PaypalCustomer object.
     * @param string $key             (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (null === $key) {
                $key = serialize(array((string) $obj->getId(), (string) $obj->getPaypalUserId()));
            } // if key === null
            self::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param mixed $value A \PayPal\Model\PaypalCustomer object or a primary key value.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && null !== $value) {
            if (is_object($value) && $value instanceof \PayPal\Model\PaypalCustomer) {
                $key = serialize(array((string) $value->getId(), (string) $value->getPaypalUserId()));

            } elseif (is_array($value) && count($value) === 2) {
                // assume we've been passed a primary key";
                $key = serialize(array((string) $value[0], (string) $value[1]));
            } elseif ($value instanceof Criteria) {
                self::$instances = [];

                return;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or \PayPal\Model\PaypalCustomer object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value, true)));
                throw $e;
            }

            unset(self::$instances[$key]);
        }
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null && $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('PaypalUserId', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return serialize(array((string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], (string) $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('PaypalUserId', TableMap::TYPE_PHPNAME, $indexType)]));
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {

            return $pks;
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? PaypalCustomerTableMap::CLASS_DEFAULT : PaypalCustomerTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     * @return array (PaypalCustomer object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = PaypalCustomerTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = PaypalCustomerTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + PaypalCustomerTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = PaypalCustomerTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            PaypalCustomerTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = PaypalCustomerTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = PaypalCustomerTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                PaypalCustomerTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(PaypalCustomerTableMap::ID);
            $criteria->addSelectColumn(PaypalCustomerTableMap::PAYPAL_USER_ID);
            $criteria->addSelectColumn(PaypalCustomerTableMap::CREDIT_CARD_ID);
            $criteria->addSelectColumn(PaypalCustomerTableMap::NAME);
            $criteria->addSelectColumn(PaypalCustomerTableMap::GIVEN_NAME);
            $criteria->addSelectColumn(PaypalCustomerTableMap::FAMILY_NAME);
            $criteria->addSelectColumn(PaypalCustomerTableMap::MIDDLE_NAME);
            $criteria->addSelectColumn(PaypalCustomerTableMap::PICTURE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::EMAIL_VERIFIED);
            $criteria->addSelectColumn(PaypalCustomerTableMap::GENDER);
            $criteria->addSelectColumn(PaypalCustomerTableMap::BIRTHDAY);
            $criteria->addSelectColumn(PaypalCustomerTableMap::ZONEINFO);
            $criteria->addSelectColumn(PaypalCustomerTableMap::LOCALE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::LANGUAGE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::VERIFIED);
            $criteria->addSelectColumn(PaypalCustomerTableMap::PHONE_NUMBER);
            $criteria->addSelectColumn(PaypalCustomerTableMap::VERIFIED_ACCOUNT);
            $criteria->addSelectColumn(PaypalCustomerTableMap::ACCOUNT_TYPE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::AGE_RANGE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::PAYER_ID);
            $criteria->addSelectColumn(PaypalCustomerTableMap::POSTAL_CODE);
            $criteria->addSelectColumn(PaypalCustomerTableMap::LOCALITY);
            $criteria->addSelectColumn(PaypalCustomerTableMap::REGION);
            $criteria->addSelectColumn(PaypalCustomerTableMap::COUNTRY);
            $criteria->addSelectColumn(PaypalCustomerTableMap::STREET_ADDRESS);
            $criteria->addSelectColumn(PaypalCustomerTableMap::CREATED_AT);
            $criteria->addSelectColumn(PaypalCustomerTableMap::UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.PAYPAL_USER_ID');
            $criteria->addSelectColumn($alias . '.CREDIT_CARD_ID');
            $criteria->addSelectColumn($alias . '.NAME');
            $criteria->addSelectColumn($alias . '.GIVEN_NAME');
            $criteria->addSelectColumn($alias . '.FAMILY_NAME');
            $criteria->addSelectColumn($alias . '.MIDDLE_NAME');
            $criteria->addSelectColumn($alias . '.PICTURE');
            $criteria->addSelectColumn($alias . '.EMAIL_VERIFIED');
            $criteria->addSelectColumn($alias . '.GENDER');
            $criteria->addSelectColumn($alias . '.BIRTHDAY');
            $criteria->addSelectColumn($alias . '.ZONEINFO');
            $criteria->addSelectColumn($alias . '.LOCALE');
            $criteria->addSelectColumn($alias . '.LANGUAGE');
            $criteria->addSelectColumn($alias . '.VERIFIED');
            $criteria->addSelectColumn($alias . '.PHONE_NUMBER');
            $criteria->addSelectColumn($alias . '.VERIFIED_ACCOUNT');
            $criteria->addSelectColumn($alias . '.ACCOUNT_TYPE');
            $criteria->addSelectColumn($alias . '.AGE_RANGE');
            $criteria->addSelectColumn($alias . '.PAYER_ID');
            $criteria->addSelectColumn($alias . '.POSTAL_CODE');
            $criteria->addSelectColumn($alias . '.LOCALITY');
            $criteria->addSelectColumn($alias . '.REGION');
            $criteria->addSelectColumn($alias . '.COUNTRY');
            $criteria->addSelectColumn($alias . '.STREET_ADDRESS');
            $criteria->addSelectColumn($alias . '.CREATED_AT');
            $criteria->addSelectColumn($alias . '.UPDATED_AT');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(PaypalCustomerTableMap::DATABASE_NAME)->getTable(PaypalCustomerTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(PaypalCustomerTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(PaypalCustomerTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new PaypalCustomerTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a PaypalCustomer or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or PaypalCustomer object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PayPal\Model\PaypalCustomer) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(PaypalCustomerTableMap::DATABASE_NAME);
            // primary key is composite; we therefore, expect
            // the primary key passed to be an array of pkey values
            if (count($values) == count($values, COUNT_RECURSIVE)) {
                // array is not multi-dimensional
                $values = array($values);
            }
            foreach ($values as $value) {
                $criterion = $criteria->getNewCriterion(PaypalCustomerTableMap::ID, $value[0]);
                $criterion->addAnd($criteria->getNewCriterion(PaypalCustomerTableMap::PAYPAL_USER_ID, $value[1]));
                $criteria->addOr($criterion);
            }
        }

        $query = PaypalCustomerQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { PaypalCustomerTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { PaypalCustomerTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the paypal_customer table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return PaypalCustomerQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a PaypalCustomer or Criteria object.
     *
     * @param mixed               $criteria Criteria or PaypalCustomer object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from PaypalCustomer object
        }


        // Set the correct dbName
        $query = PaypalCustomerQuery::create()->mergeWith($criteria);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = $query->doInsert($con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

} // PaypalCustomerTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
PaypalCustomerTableMap::buildTableMap();
