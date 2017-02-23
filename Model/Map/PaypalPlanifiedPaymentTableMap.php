<?php

namespace PayPal\Model\Map;

use PayPal\Model\PaypalPlanifiedPayment;
use PayPal\Model\PaypalPlanifiedPaymentQuery;
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
 * This class defines the structure of the 'paypal_planified_payment' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class PaypalPlanifiedPaymentTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'PayPal.Model.Map.PaypalPlanifiedPaymentTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'paypal_planified_payment';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\PayPal\\Model\\PaypalPlanifiedPayment';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'PayPal.Model.PaypalPlanifiedPayment';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 9;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 9;

    /**
     * the column name for the ID field
     */
    const ID = 'paypal_planified_payment.ID';

    /**
     * the column name for the FREQUENCY field
     */
    const FREQUENCY = 'paypal_planified_payment.FREQUENCY';

    /**
     * the column name for the FREQUENCY_INTERVAL field
     */
    const FREQUENCY_INTERVAL = 'paypal_planified_payment.FREQUENCY_INTERVAL';

    /**
     * the column name for the CYCLE field
     */
    const CYCLE = 'paypal_planified_payment.CYCLE';

    /**
     * the column name for the MIN_AMOUNT field
     */
    const MIN_AMOUNT = 'paypal_planified_payment.MIN_AMOUNT';

    /**
     * the column name for the MAX_AMOUNT field
     */
    const MAX_AMOUNT = 'paypal_planified_payment.MAX_AMOUNT';

    /**
     * the column name for the POSITION field
     */
    const POSITION = 'paypal_planified_payment.POSITION';

    /**
     * the column name for the CREATED_AT field
     */
    const CREATED_AT = 'paypal_planified_payment.CREATED_AT';

    /**
     * the column name for the UPDATED_AT field
     */
    const UPDATED_AT = 'paypal_planified_payment.UPDATED_AT';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    // i18n behavior

    /**
     * The default locale to use for translations.
     *
     * @var string
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Frequency', 'FrequencyInterval', 'Cycle', 'MinAmount', 'MaxAmount', 'Position', 'CreatedAt', 'UpdatedAt', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'frequency', 'frequencyInterval', 'cycle', 'minAmount', 'maxAmount', 'position', 'createdAt', 'updatedAt', ),
        self::TYPE_COLNAME       => array(PaypalPlanifiedPaymentTableMap::ID, PaypalPlanifiedPaymentTableMap::FREQUENCY, PaypalPlanifiedPaymentTableMap::FREQUENCY_INTERVAL, PaypalPlanifiedPaymentTableMap::CYCLE, PaypalPlanifiedPaymentTableMap::MIN_AMOUNT, PaypalPlanifiedPaymentTableMap::MAX_AMOUNT, PaypalPlanifiedPaymentTableMap::POSITION, PaypalPlanifiedPaymentTableMap::CREATED_AT, PaypalPlanifiedPaymentTableMap::UPDATED_AT, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'FREQUENCY', 'FREQUENCY_INTERVAL', 'CYCLE', 'MIN_AMOUNT', 'MAX_AMOUNT', 'POSITION', 'CREATED_AT', 'UPDATED_AT', ),
        self::TYPE_FIELDNAME     => array('id', 'frequency', 'frequency_interval', 'cycle', 'min_amount', 'max_amount', 'position', 'created_at', 'updated_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Frequency' => 1, 'FrequencyInterval' => 2, 'Cycle' => 3, 'MinAmount' => 4, 'MaxAmount' => 5, 'Position' => 6, 'CreatedAt' => 7, 'UpdatedAt' => 8, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'frequency' => 1, 'frequencyInterval' => 2, 'cycle' => 3, 'minAmount' => 4, 'maxAmount' => 5, 'position' => 6, 'createdAt' => 7, 'updatedAt' => 8, ),
        self::TYPE_COLNAME       => array(PaypalPlanifiedPaymentTableMap::ID => 0, PaypalPlanifiedPaymentTableMap::FREQUENCY => 1, PaypalPlanifiedPaymentTableMap::FREQUENCY_INTERVAL => 2, PaypalPlanifiedPaymentTableMap::CYCLE => 3, PaypalPlanifiedPaymentTableMap::MIN_AMOUNT => 4, PaypalPlanifiedPaymentTableMap::MAX_AMOUNT => 5, PaypalPlanifiedPaymentTableMap::POSITION => 6, PaypalPlanifiedPaymentTableMap::CREATED_AT => 7, PaypalPlanifiedPaymentTableMap::UPDATED_AT => 8, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'FREQUENCY' => 1, 'FREQUENCY_INTERVAL' => 2, 'CYCLE' => 3, 'MIN_AMOUNT' => 4, 'MAX_AMOUNT' => 5, 'POSITION' => 6, 'CREATED_AT' => 7, 'UPDATED_AT' => 8, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'frequency' => 1, 'frequency_interval' => 2, 'cycle' => 3, 'min_amount' => 4, 'max_amount' => 5, 'position' => 6, 'created_at' => 7, 'updated_at' => 8, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, )
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
        $this->setName('paypal_planified_payment');
        $this->setPhpName('PaypalPlanifiedPayment');
        $this->setClassName('\\PayPal\\Model\\PaypalPlanifiedPayment');
        $this->setPackage('PayPal.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('FREQUENCY', 'Frequency', 'VARCHAR', true, 255, null);
        $this->addColumn('FREQUENCY_INTERVAL', 'FrequencyInterval', 'INTEGER', true, null, null);
        $this->addColumn('CYCLE', 'Cycle', 'INTEGER', true, null, null);
        $this->addColumn('MIN_AMOUNT', 'MinAmount', 'DECIMAL', false, 16, 0);
        $this->addColumn('MAX_AMOUNT', 'MaxAmount', 'DECIMAL', false, 16, 0);
        $this->addColumn('POSITION', 'Position', 'INTEGER', true, null, 0);
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('PaypalCart', '\\PayPal\\Model\\PaypalCart', RelationMap::ONE_TO_MANY, array('id' => 'planified_payment_id', ), 'CASCADE', 'RESTRICT', 'PaypalCarts');
        $this->addRelation('PaypalPlanifiedPaymentI18n', '\\PayPal\\Model\\PaypalPlanifiedPaymentI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null, 'PaypalPlanifiedPaymentI18ns');
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
            'i18n' => array('i18n_table' => '%TABLE%_i18n', 'i18n_phpname' => '%PHPNAME%I18n', 'i18n_columns' => 'title, description', 'locale_column' => 'locale', 'locale_length' => '5', 'default_locale' => '', 'locale_alias' => '', ),
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', ),
        );
    } // getBehaviors()
    /**
     * Method to invalidate the instance pool of all tables related to paypal_planified_payment     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in ".$this->getClassNameFromBuilder($joinedTableTableMapBuilder)." instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
                PaypalCartTableMap::clearInstancePool();
                PaypalPlanifiedPaymentI18nTableMap::clearInstancePool();
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
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

            return (int) $row[
                            $indexType == TableMap::TYPE_NUM
                            ? 0 + $offset
                            : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
                        ];
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
        return $withPrefix ? PaypalPlanifiedPaymentTableMap::CLASS_DEFAULT : PaypalPlanifiedPaymentTableMap::OM_CLASS;
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
     * @return array (PaypalPlanifiedPayment object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = PaypalPlanifiedPaymentTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = PaypalPlanifiedPaymentTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + PaypalPlanifiedPaymentTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = PaypalPlanifiedPaymentTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            PaypalPlanifiedPaymentTableMap::addInstanceToPool($obj, $key);
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
            $key = PaypalPlanifiedPaymentTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = PaypalPlanifiedPaymentTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                PaypalPlanifiedPaymentTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::ID);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::FREQUENCY);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::FREQUENCY_INTERVAL);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::CYCLE);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::MIN_AMOUNT);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::MAX_AMOUNT);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::POSITION);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::CREATED_AT);
            $criteria->addSelectColumn(PaypalPlanifiedPaymentTableMap::UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.FREQUENCY');
            $criteria->addSelectColumn($alias . '.FREQUENCY_INTERVAL');
            $criteria->addSelectColumn($alias . '.CYCLE');
            $criteria->addSelectColumn($alias . '.MIN_AMOUNT');
            $criteria->addSelectColumn($alias . '.MAX_AMOUNT');
            $criteria->addSelectColumn($alias . '.POSITION');
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
        return Propel::getServiceContainer()->getDatabaseMap(PaypalPlanifiedPaymentTableMap::DATABASE_NAME)->getTable(PaypalPlanifiedPaymentTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(PaypalPlanifiedPaymentTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(PaypalPlanifiedPaymentTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new PaypalPlanifiedPaymentTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a PaypalPlanifiedPayment or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or PaypalPlanifiedPayment object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalPlanifiedPaymentTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PayPal\Model\PaypalPlanifiedPayment) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(PaypalPlanifiedPaymentTableMap::DATABASE_NAME);
            $criteria->add(PaypalPlanifiedPaymentTableMap::ID, (array) $values, Criteria::IN);
        }

        $query = PaypalPlanifiedPaymentQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { PaypalPlanifiedPaymentTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { PaypalPlanifiedPaymentTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the paypal_planified_payment table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return PaypalPlanifiedPaymentQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a PaypalPlanifiedPayment or Criteria object.
     *
     * @param mixed               $criteria Criteria or PaypalPlanifiedPayment object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalPlanifiedPaymentTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from PaypalPlanifiedPayment object
        }

        if ($criteria->containsKey(PaypalPlanifiedPaymentTableMap::ID) && $criteria->keyContainsValue(PaypalPlanifiedPaymentTableMap::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.PaypalPlanifiedPaymentTableMap::ID.')');
        }


        // Set the correct dbName
        $query = PaypalPlanifiedPaymentQuery::create()->mergeWith($criteria);

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

} // PaypalPlanifiedPaymentTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
PaypalPlanifiedPaymentTableMap::buildTableMap();
