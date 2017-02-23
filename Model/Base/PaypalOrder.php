<?php

namespace PayPal\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use PayPal\Model\PaypalOrder as ChildPaypalOrder;
use PayPal\Model\PaypalOrderQuery as ChildPaypalOrderQuery;
use PayPal\Model\PaypalOrderVersion as ChildPaypalOrderVersion;
use PayPal\Model\PaypalOrderVersionQuery as ChildPaypalOrderVersionQuery;
use PayPal\Model\PaypalPlan as ChildPaypalPlan;
use PayPal\Model\PaypalPlanQuery as ChildPaypalPlanQuery;
use PayPal\Model\Map\PaypalOrderTableMap;
use PayPal\Model\Map\PaypalOrderVersionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Thelia\Model\Order as ChildOrder;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderVersionQuery;

abstract class PaypalOrder implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\PayPal\\Model\\Map\\PaypalOrderTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the payment_id field.
     * @var        string
     */
    protected $payment_id;

    /**
     * The value for the agreement_id field.
     * @var        string
     */
    protected $agreement_id;

    /**
     * The value for the credit_card_id field.
     * @var        string
     */
    protected $credit_card_id;

    /**
     * The value for the state field.
     * @var        string
     */
    protected $state;

    /**
     * The value for the amount field.
     * Note: this column has a database default value of: '0.000000'
     * @var        string
     */
    protected $amount;

    /**
     * The value for the description field.
     * @var        string
     */
    protected $description;

    /**
     * The value for the payer_id field.
     * @var        string
     */
    protected $payer_id;

    /**
     * The value for the token field.
     * @var        string
     */
    protected $token;

    /**
     * The value for the planified_title field.
     * @var        string
     */
    protected $planified_title;

    /**
     * The value for the planified_description field.
     * @var        string
     */
    protected $planified_description;

    /**
     * The value for the planified_frequency field.
     * @var        string
     */
    protected $planified_frequency;

    /**
     * The value for the planified_frequency_interval field.
     * @var        int
     */
    protected $planified_frequency_interval;

    /**
     * The value for the planified_cycle field.
     * @var        int
     */
    protected $planified_cycle;

    /**
     * The value for the planified_actual_cycle field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $planified_actual_cycle;

    /**
     * The value for the planified_min_amount field.
     * Note: this column has a database default value of: '0.000000'
     * @var        string
     */
    protected $planified_min_amount;

    /**
     * The value for the planified_max_amount field.
     * Note: this column has a database default value of: '0.000000'
     * @var        string
     */
    protected $planified_max_amount;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * The value for the version field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $version;

    /**
     * The value for the version_created_at field.
     * @var        string
     */
    protected $version_created_at;

    /**
     * The value for the version_created_by field.
     * @var        string
     */
    protected $version_created_by;

    /**
     * @var        Order
     */
    protected $aOrder;

    /**
     * @var        ObjectCollection|ChildPaypalPlan[] Collection to store aggregation of ChildPaypalPlan objects.
     */
    protected $collPaypalPlans;
    protected $collPaypalPlansPartial;

    /**
     * @var        ObjectCollection|ChildPaypalOrderVersion[] Collection to store aggregation of ChildPaypalOrderVersion objects.
     */
    protected $collPaypalOrderVersions;
    protected $collPaypalOrderVersionsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // versionable behavior


    /**
     * @var bool
     */
    protected $enforceVersion = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $paypalPlansScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $paypalOrderVersionsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->amount = '0.000000';
        $this->planified_actual_cycle = 0;
        $this->planified_min_amount = '0.000000';
        $this->planified_max_amount = '0.000000';
        $this->version = 0;
    }

    /**
     * Initializes internal state of PayPal\Model\Base\PaypalOrder object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>PaypalOrder</code> instance.  If
     * <code>obj</code> is an instance of <code>PaypalOrder</code>, delegates to
     * <code>equals(PaypalOrder)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return PaypalOrder The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return PaypalOrder The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [payment_id] column value.
     *
     * @return   string
     */
    public function getPaymentId()
    {

        return $this->payment_id;
    }

    /**
     * Get the [agreement_id] column value.
     *
     * @return   string
     */
    public function getAgreementId()
    {

        return $this->agreement_id;
    }

    /**
     * Get the [credit_card_id] column value.
     *
     * @return   string
     */
    public function getCreditCardId()
    {

        return $this->credit_card_id;
    }

    /**
     * Get the [state] column value.
     *
     * @return   string
     */
    public function getState()
    {

        return $this->state;
    }

    /**
     * Get the [amount] column value.
     *
     * @return   string
     */
    public function getAmount()
    {

        return $this->amount;
    }

    /**
     * Get the [description] column value.
     *
     * @return   string
     */
    public function getDescription()
    {

        return $this->description;
    }

    /**
     * Get the [payer_id] column value.
     *
     * @return   string
     */
    public function getPayerId()
    {

        return $this->payer_id;
    }

    /**
     * Get the [token] column value.
     *
     * @return   string
     */
    public function getToken()
    {

        return $this->token;
    }

    /**
     * Get the [planified_title] column value.
     *
     * @return   string
     */
    public function getPlanifiedTitle()
    {

        return $this->planified_title;
    }

    /**
     * Get the [planified_description] column value.
     *
     * @return   string
     */
    public function getPlanifiedDescription()
    {

        return $this->planified_description;
    }

    /**
     * Get the [planified_frequency] column value.
     *
     * @return   string
     */
    public function getPlanifiedFrequency()
    {

        return $this->planified_frequency;
    }

    /**
     * Get the [planified_frequency_interval] column value.
     *
     * @return   int
     */
    public function getPlanifiedFrequencyInterval()
    {

        return $this->planified_frequency_interval;
    }

    /**
     * Get the [planified_cycle] column value.
     *
     * @return   int
     */
    public function getPlanifiedCycle()
    {

        return $this->planified_cycle;
    }

    /**
     * Get the [planified_actual_cycle] column value.
     *
     * @return   int
     */
    public function getPlanifiedActualCycle()
    {

        return $this->planified_actual_cycle;
    }

    /**
     * Get the [planified_min_amount] column value.
     *
     * @return   string
     */
    public function getPlanifiedMinAmount()
    {

        return $this->planified_min_amount;
    }

    /**
     * Get the [planified_max_amount] column value.
     *
     * @return   string
     */
    public function getPlanifiedMaxAmount()
    {

        return $this->planified_max_amount;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTime ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTime ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Get the [version] column value.
     *
     * @return   int
     */
    public function getVersion()
    {

        return $this->version;
    }

    /**
     * Get the [optionally formatted] temporal [version_created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getVersionCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->version_created_at;
        } else {
            return $this->version_created_at instanceof \DateTime ? $this->version_created_at->format($format) : null;
        }
    }

    /**
     * Get the [version_created_by] column value.
     *
     * @return   string
     */
    public function getVersionCreatedBy()
    {

        return $this->version_created_by;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[PaypalOrderTableMap::ID] = true;
        }

        if ($this->aOrder !== null && $this->aOrder->getId() !== $v) {
            $this->aOrder = null;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [payment_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPaymentId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->payment_id !== $v) {
            $this->payment_id = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PAYMENT_ID] = true;
        }


        return $this;
    } // setPaymentId()

    /**
     * Set the value of [agreement_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setAgreementId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->agreement_id !== $v) {
            $this->agreement_id = $v;
            $this->modifiedColumns[PaypalOrderTableMap::AGREEMENT_ID] = true;
        }


        return $this;
    } // setAgreementId()

    /**
     * Set the value of [credit_card_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setCreditCardId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->credit_card_id !== $v) {
            $this->credit_card_id = $v;
            $this->modifiedColumns[PaypalOrderTableMap::CREDIT_CARD_ID] = true;
        }


        return $this;
    } // setCreditCardId()

    /**
     * Set the value of [state] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setState($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->state !== $v) {
            $this->state = $v;
            $this->modifiedColumns[PaypalOrderTableMap::STATE] = true;
        }


        return $this;
    } // setState()

    /**
     * Set the value of [amount] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setAmount($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->amount !== $v) {
            $this->amount = $v;
            $this->modifiedColumns[PaypalOrderTableMap::AMOUNT] = true;
        }


        return $this;
    } // setAmount()

    /**
     * Set the value of [description] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setDescription($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->description !== $v) {
            $this->description = $v;
            $this->modifiedColumns[PaypalOrderTableMap::DESCRIPTION] = true;
        }


        return $this;
    } // setDescription()

    /**
     * Set the value of [payer_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPayerId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->payer_id !== $v) {
            $this->payer_id = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PAYER_ID] = true;
        }


        return $this;
    } // setPayerId()

    /**
     * Set the value of [token] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setToken($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->token !== $v) {
            $this->token = $v;
            $this->modifiedColumns[PaypalOrderTableMap::TOKEN] = true;
        }


        return $this;
    } // setToken()

    /**
     * Set the value of [planified_title] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedTitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->planified_title !== $v) {
            $this->planified_title = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_TITLE] = true;
        }


        return $this;
    } // setPlanifiedTitle()

    /**
     * Set the value of [planified_description] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedDescription($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->planified_description !== $v) {
            $this->planified_description = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_DESCRIPTION] = true;
        }


        return $this;
    } // setPlanifiedDescription()

    /**
     * Set the value of [planified_frequency] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedFrequency($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->planified_frequency !== $v) {
            $this->planified_frequency = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_FREQUENCY] = true;
        }


        return $this;
    } // setPlanifiedFrequency()

    /**
     * Set the value of [planified_frequency_interval] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedFrequencyInterval($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->planified_frequency_interval !== $v) {
            $this->planified_frequency_interval = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL] = true;
        }


        return $this;
    } // setPlanifiedFrequencyInterval()

    /**
     * Set the value of [planified_cycle] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedCycle($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->planified_cycle !== $v) {
            $this->planified_cycle = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_CYCLE] = true;
        }


        return $this;
    } // setPlanifiedCycle()

    /**
     * Set the value of [planified_actual_cycle] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedActualCycle($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->planified_actual_cycle !== $v) {
            $this->planified_actual_cycle = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE] = true;
        }


        return $this;
    } // setPlanifiedActualCycle()

    /**
     * Set the value of [planified_min_amount] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedMinAmount($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->planified_min_amount !== $v) {
            $this->planified_min_amount = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT] = true;
        }


        return $this;
    } // setPlanifiedMinAmount()

    /**
     * Set the value of [planified_max_amount] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setPlanifiedMaxAmount($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->planified_max_amount !== $v) {
            $this->planified_max_amount = $v;
            $this->modifiedColumns[PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT] = true;
        }


        return $this;
    } // setPlanifiedMaxAmount()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[PaypalOrderTableMap::CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[PaypalOrderTableMap::UPDATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

    /**
     * Set the value of [version] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setVersion($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->version !== $v) {
            $this->version = $v;
            $this->modifiedColumns[PaypalOrderTableMap::VERSION] = true;
        }


        return $this;
    } // setVersion()

    /**
     * Sets the value of [version_created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setVersionCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->version_created_at !== null || $dt !== null) {
            if ($dt !== $this->version_created_at) {
                $this->version_created_at = $dt;
                $this->modifiedColumns[PaypalOrderTableMap::VERSION_CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setVersionCreatedAt()

    /**
     * Set the value of [version_created_by] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function setVersionCreatedBy($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->version_created_by !== $v) {
            $this->version_created_by = $v;
            $this->modifiedColumns[PaypalOrderTableMap::VERSION_CREATED_BY] = true;
        }


        return $this;
    } // setVersionCreatedBy()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->amount !== '0.000000') {
                return false;
            }

            if ($this->planified_actual_cycle !== 0) {
                return false;
            }

            if ($this->planified_min_amount !== '0.000000') {
                return false;
            }

            if ($this->planified_max_amount !== '0.000000') {
                return false;
            }

            if ($this->version !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : PaypalOrderTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : PaypalOrderTableMap::translateFieldName('PaymentId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->payment_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : PaypalOrderTableMap::translateFieldName('AgreementId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->agreement_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : PaypalOrderTableMap::translateFieldName('CreditCardId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->credit_card_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : PaypalOrderTableMap::translateFieldName('State', TableMap::TYPE_PHPNAME, $indexType)];
            $this->state = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : PaypalOrderTableMap::translateFieldName('Amount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->amount = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : PaypalOrderTableMap::translateFieldName('Description', TableMap::TYPE_PHPNAME, $indexType)];
            $this->description = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : PaypalOrderTableMap::translateFieldName('PayerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->payer_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : PaypalOrderTableMap::translateFieldName('Token', TableMap::TYPE_PHPNAME, $indexType)];
            $this->token = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedTitle', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_title = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedDescription', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_description = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedFrequency', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_frequency = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedFrequencyInterval', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_frequency_interval = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 13 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedCycle', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_cycle = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 14 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedActualCycle', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_actual_cycle = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 15 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedMinAmount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_min_amount = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 16 + $startcol : PaypalOrderTableMap::translateFieldName('PlanifiedMaxAmount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->planified_max_amount = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 17 + $startcol : PaypalOrderTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 18 + $startcol : PaypalOrderTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 19 + $startcol : PaypalOrderTableMap::translateFieldName('Version', TableMap::TYPE_PHPNAME, $indexType)];
            $this->version = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 20 + $startcol : PaypalOrderTableMap::translateFieldName('VersionCreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->version_created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 21 + $startcol : PaypalOrderTableMap::translateFieldName('VersionCreatedBy', TableMap::TYPE_PHPNAME, $indexType)];
            $this->version_created_by = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 22; // 22 = PaypalOrderTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \PayPal\Model\PaypalOrder object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aOrder !== null && $this->id !== $this->aOrder->getId()) {
            $this->aOrder = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PaypalOrderTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildPaypalOrderQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aOrder = null;
            $this->collPaypalPlans = null;

            $this->collPaypalOrderVersions = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see PaypalOrder::setDeleted()
     * @see PaypalOrder::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildPaypalOrderQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalOrderTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // versionable behavior
            if ($this->isVersioningNecessary()) {
                $this->setVersion($this->isNew() ? 1 : $this->getLastVersionNumber($con) + 1);
                if (!$this->isColumnModified(PaypalOrderTableMap::VERSION_CREATED_AT)) {
                    $this->setVersionCreatedAt(time());
                }
                $createVersion = true; // for postSave hook
            }
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(PaypalOrderTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(PaypalOrderTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(PaypalOrderTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                // versionable behavior
                if (isset($createVersion)) {
                    $this->addVersion($con);
                }
                PaypalOrderTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aOrder !== null) {
                if ($this->aOrder->isModified() || $this->aOrder->isNew()) {
                    $affectedRows += $this->aOrder->save($con);
                }
                $this->setOrder($this->aOrder);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->paypalPlansScheduledForDeletion !== null) {
                if (!$this->paypalPlansScheduledForDeletion->isEmpty()) {
                    \PayPal\Model\PaypalPlanQuery::create()
                        ->filterByPrimaryKeys($this->paypalPlansScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->paypalPlansScheduledForDeletion = null;
                }
            }

                if ($this->collPaypalPlans !== null) {
            foreach ($this->collPaypalPlans as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->paypalOrderVersionsScheduledForDeletion !== null) {
                if (!$this->paypalOrderVersionsScheduledForDeletion->isEmpty()) {
                    \PayPal\Model\PaypalOrderVersionQuery::create()
                        ->filterByPrimaryKeys($this->paypalOrderVersionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->paypalOrderVersionsScheduledForDeletion = null;
                }
            }

                if ($this->collPaypalOrderVersions !== null) {
            foreach ($this->collPaypalOrderVersions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(PaypalOrderTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PAYMENT_ID)) {
            $modifiedColumns[':p' . $index++]  = 'PAYMENT_ID';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::AGREEMENT_ID)) {
            $modifiedColumns[':p' . $index++]  = 'AGREEMENT_ID';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::CREDIT_CARD_ID)) {
            $modifiedColumns[':p' . $index++]  = 'CREDIT_CARD_ID';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::STATE)) {
            $modifiedColumns[':p' . $index++]  = 'STATE';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'AMOUNT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::DESCRIPTION)) {
            $modifiedColumns[':p' . $index++]  = 'DESCRIPTION';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PAYER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'PAYER_ID';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::TOKEN)) {
            $modifiedColumns[':p' . $index++]  = 'TOKEN';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_TITLE)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_TITLE';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_DESCRIPTION)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_DESCRIPTION';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_FREQUENCY)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_FREQUENCY';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_FREQUENCY_INTERVAL';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_CYCLE)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_CYCLE';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_ACTUAL_CYCLE';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_MIN_AMOUNT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'PLANIFIED_MAX_AMOUNT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION_CREATED_AT';
        }
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION_CREATED_BY)) {
            $modifiedColumns[':p' . $index++]  = 'VERSION_CREATED_BY';
        }

        $sql = sprintf(
            'INSERT INTO paypal_order (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'PAYMENT_ID':
                        $stmt->bindValue($identifier, $this->payment_id, PDO::PARAM_STR);
                        break;
                    case 'AGREEMENT_ID':
                        $stmt->bindValue($identifier, $this->agreement_id, PDO::PARAM_STR);
                        break;
                    case 'CREDIT_CARD_ID':
                        $stmt->bindValue($identifier, $this->credit_card_id, PDO::PARAM_STR);
                        break;
                    case 'STATE':
                        $stmt->bindValue($identifier, $this->state, PDO::PARAM_STR);
                        break;
                    case 'AMOUNT':
                        $stmt->bindValue($identifier, $this->amount, PDO::PARAM_STR);
                        break;
                    case 'DESCRIPTION':
                        $stmt->bindValue($identifier, $this->description, PDO::PARAM_STR);
                        break;
                    case 'PAYER_ID':
                        $stmt->bindValue($identifier, $this->payer_id, PDO::PARAM_STR);
                        break;
                    case 'TOKEN':
                        $stmt->bindValue($identifier, $this->token, PDO::PARAM_STR);
                        break;
                    case 'PLANIFIED_TITLE':
                        $stmt->bindValue($identifier, $this->planified_title, PDO::PARAM_STR);
                        break;
                    case 'PLANIFIED_DESCRIPTION':
                        $stmt->bindValue($identifier, $this->planified_description, PDO::PARAM_STR);
                        break;
                    case 'PLANIFIED_FREQUENCY':
                        $stmt->bindValue($identifier, $this->planified_frequency, PDO::PARAM_STR);
                        break;
                    case 'PLANIFIED_FREQUENCY_INTERVAL':
                        $stmt->bindValue($identifier, $this->planified_frequency_interval, PDO::PARAM_INT);
                        break;
                    case 'PLANIFIED_CYCLE':
                        $stmt->bindValue($identifier, $this->planified_cycle, PDO::PARAM_INT);
                        break;
                    case 'PLANIFIED_ACTUAL_CYCLE':
                        $stmt->bindValue($identifier, $this->planified_actual_cycle, PDO::PARAM_INT);
                        break;
                    case 'PLANIFIED_MIN_AMOUNT':
                        $stmt->bindValue($identifier, $this->planified_min_amount, PDO::PARAM_STR);
                        break;
                    case 'PLANIFIED_MAX_AMOUNT':
                        $stmt->bindValue($identifier, $this->planified_max_amount, PDO::PARAM_STR);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'VERSION':
                        $stmt->bindValue($identifier, $this->version, PDO::PARAM_INT);
                        break;
                    case 'VERSION_CREATED_AT':
                        $stmt->bindValue($identifier, $this->version_created_at ? $this->version_created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'VERSION_CREATED_BY':
                        $stmt->bindValue($identifier, $this->version_created_by, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = PaypalOrderTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getPaymentId();
                break;
            case 2:
                return $this->getAgreementId();
                break;
            case 3:
                return $this->getCreditCardId();
                break;
            case 4:
                return $this->getState();
                break;
            case 5:
                return $this->getAmount();
                break;
            case 6:
                return $this->getDescription();
                break;
            case 7:
                return $this->getPayerId();
                break;
            case 8:
                return $this->getToken();
                break;
            case 9:
                return $this->getPlanifiedTitle();
                break;
            case 10:
                return $this->getPlanifiedDescription();
                break;
            case 11:
                return $this->getPlanifiedFrequency();
                break;
            case 12:
                return $this->getPlanifiedFrequencyInterval();
                break;
            case 13:
                return $this->getPlanifiedCycle();
                break;
            case 14:
                return $this->getPlanifiedActualCycle();
                break;
            case 15:
                return $this->getPlanifiedMinAmount();
                break;
            case 16:
                return $this->getPlanifiedMaxAmount();
                break;
            case 17:
                return $this->getCreatedAt();
                break;
            case 18:
                return $this->getUpdatedAt();
                break;
            case 19:
                return $this->getVersion();
                break;
            case 20:
                return $this->getVersionCreatedAt();
                break;
            case 21:
                return $this->getVersionCreatedBy();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['PaypalOrder'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['PaypalOrder'][$this->getPrimaryKey()] = true;
        $keys = PaypalOrderTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getPaymentId(),
            $keys[2] => $this->getAgreementId(),
            $keys[3] => $this->getCreditCardId(),
            $keys[4] => $this->getState(),
            $keys[5] => $this->getAmount(),
            $keys[6] => $this->getDescription(),
            $keys[7] => $this->getPayerId(),
            $keys[8] => $this->getToken(),
            $keys[9] => $this->getPlanifiedTitle(),
            $keys[10] => $this->getPlanifiedDescription(),
            $keys[11] => $this->getPlanifiedFrequency(),
            $keys[12] => $this->getPlanifiedFrequencyInterval(),
            $keys[13] => $this->getPlanifiedCycle(),
            $keys[14] => $this->getPlanifiedActualCycle(),
            $keys[15] => $this->getPlanifiedMinAmount(),
            $keys[16] => $this->getPlanifiedMaxAmount(),
            $keys[17] => $this->getCreatedAt(),
            $keys[18] => $this->getUpdatedAt(),
            $keys[19] => $this->getVersion(),
            $keys[20] => $this->getVersionCreatedAt(),
            $keys[21] => $this->getVersionCreatedBy(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aOrder) {
                $result['Order'] = $this->aOrder->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collPaypalPlans) {
                $result['PaypalPlans'] = $this->collPaypalPlans->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPaypalOrderVersions) {
                $result['PaypalOrderVersions'] = $this->collPaypalOrderVersions->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = PaypalOrderTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setPaymentId($value);
                break;
            case 2:
                $this->setAgreementId($value);
                break;
            case 3:
                $this->setCreditCardId($value);
                break;
            case 4:
                $this->setState($value);
                break;
            case 5:
                $this->setAmount($value);
                break;
            case 6:
                $this->setDescription($value);
                break;
            case 7:
                $this->setPayerId($value);
                break;
            case 8:
                $this->setToken($value);
                break;
            case 9:
                $this->setPlanifiedTitle($value);
                break;
            case 10:
                $this->setPlanifiedDescription($value);
                break;
            case 11:
                $this->setPlanifiedFrequency($value);
                break;
            case 12:
                $this->setPlanifiedFrequencyInterval($value);
                break;
            case 13:
                $this->setPlanifiedCycle($value);
                break;
            case 14:
                $this->setPlanifiedActualCycle($value);
                break;
            case 15:
                $this->setPlanifiedMinAmount($value);
                break;
            case 16:
                $this->setPlanifiedMaxAmount($value);
                break;
            case 17:
                $this->setCreatedAt($value);
                break;
            case 18:
                $this->setUpdatedAt($value);
                break;
            case 19:
                $this->setVersion($value);
                break;
            case 20:
                $this->setVersionCreatedAt($value);
                break;
            case 21:
                $this->setVersionCreatedBy($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = PaypalOrderTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setPaymentId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAgreementId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setCreditCardId($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setState($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setAmount($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setDescription($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setPayerId($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setToken($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setPlanifiedTitle($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setPlanifiedDescription($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setPlanifiedFrequency($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setPlanifiedFrequencyInterval($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setPlanifiedCycle($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setPlanifiedActualCycle($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setPlanifiedMinAmount($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setPlanifiedMaxAmount($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setCreatedAt($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setUpdatedAt($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setVersion($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setVersionCreatedAt($arr[$keys[20]]);
        if (array_key_exists($keys[21], $arr)) $this->setVersionCreatedBy($arr[$keys[21]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(PaypalOrderTableMap::DATABASE_NAME);

        if ($this->isColumnModified(PaypalOrderTableMap::ID)) $criteria->add(PaypalOrderTableMap::ID, $this->id);
        if ($this->isColumnModified(PaypalOrderTableMap::PAYMENT_ID)) $criteria->add(PaypalOrderTableMap::PAYMENT_ID, $this->payment_id);
        if ($this->isColumnModified(PaypalOrderTableMap::AGREEMENT_ID)) $criteria->add(PaypalOrderTableMap::AGREEMENT_ID, $this->agreement_id);
        if ($this->isColumnModified(PaypalOrderTableMap::CREDIT_CARD_ID)) $criteria->add(PaypalOrderTableMap::CREDIT_CARD_ID, $this->credit_card_id);
        if ($this->isColumnModified(PaypalOrderTableMap::STATE)) $criteria->add(PaypalOrderTableMap::STATE, $this->state);
        if ($this->isColumnModified(PaypalOrderTableMap::AMOUNT)) $criteria->add(PaypalOrderTableMap::AMOUNT, $this->amount);
        if ($this->isColumnModified(PaypalOrderTableMap::DESCRIPTION)) $criteria->add(PaypalOrderTableMap::DESCRIPTION, $this->description);
        if ($this->isColumnModified(PaypalOrderTableMap::PAYER_ID)) $criteria->add(PaypalOrderTableMap::PAYER_ID, $this->payer_id);
        if ($this->isColumnModified(PaypalOrderTableMap::TOKEN)) $criteria->add(PaypalOrderTableMap::TOKEN, $this->token);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_TITLE)) $criteria->add(PaypalOrderTableMap::PLANIFIED_TITLE, $this->planified_title);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_DESCRIPTION)) $criteria->add(PaypalOrderTableMap::PLANIFIED_DESCRIPTION, $this->planified_description);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_FREQUENCY)) $criteria->add(PaypalOrderTableMap::PLANIFIED_FREQUENCY, $this->planified_frequency);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL)) $criteria->add(PaypalOrderTableMap::PLANIFIED_FREQUENCY_INTERVAL, $this->planified_frequency_interval);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_CYCLE)) $criteria->add(PaypalOrderTableMap::PLANIFIED_CYCLE, $this->planified_cycle);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE)) $criteria->add(PaypalOrderTableMap::PLANIFIED_ACTUAL_CYCLE, $this->planified_actual_cycle);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT)) $criteria->add(PaypalOrderTableMap::PLANIFIED_MIN_AMOUNT, $this->planified_min_amount);
        if ($this->isColumnModified(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT)) $criteria->add(PaypalOrderTableMap::PLANIFIED_MAX_AMOUNT, $this->planified_max_amount);
        if ($this->isColumnModified(PaypalOrderTableMap::CREATED_AT)) $criteria->add(PaypalOrderTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(PaypalOrderTableMap::UPDATED_AT)) $criteria->add(PaypalOrderTableMap::UPDATED_AT, $this->updated_at);
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION)) $criteria->add(PaypalOrderTableMap::VERSION, $this->version);
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION_CREATED_AT)) $criteria->add(PaypalOrderTableMap::VERSION_CREATED_AT, $this->version_created_at);
        if ($this->isColumnModified(PaypalOrderTableMap::VERSION_CREATED_BY)) $criteria->add(PaypalOrderTableMap::VERSION_CREATED_BY, $this->version_created_by);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(PaypalOrderTableMap::DATABASE_NAME);
        $criteria->add(PaypalOrderTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \PayPal\Model\PaypalOrder (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setId($this->getId());
        $copyObj->setPaymentId($this->getPaymentId());
        $copyObj->setAgreementId($this->getAgreementId());
        $copyObj->setCreditCardId($this->getCreditCardId());
        $copyObj->setState($this->getState());
        $copyObj->setAmount($this->getAmount());
        $copyObj->setDescription($this->getDescription());
        $copyObj->setPayerId($this->getPayerId());
        $copyObj->setToken($this->getToken());
        $copyObj->setPlanifiedTitle($this->getPlanifiedTitle());
        $copyObj->setPlanifiedDescription($this->getPlanifiedDescription());
        $copyObj->setPlanifiedFrequency($this->getPlanifiedFrequency());
        $copyObj->setPlanifiedFrequencyInterval($this->getPlanifiedFrequencyInterval());
        $copyObj->setPlanifiedCycle($this->getPlanifiedCycle());
        $copyObj->setPlanifiedActualCycle($this->getPlanifiedActualCycle());
        $copyObj->setPlanifiedMinAmount($this->getPlanifiedMinAmount());
        $copyObj->setPlanifiedMaxAmount($this->getPlanifiedMaxAmount());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());
        $copyObj->setVersion($this->getVersion());
        $copyObj->setVersionCreatedAt($this->getVersionCreatedAt());
        $copyObj->setVersionCreatedBy($this->getVersionCreatedBy());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getPaypalPlans() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPaypalPlan($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPaypalOrderVersions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPaypalOrderVersion($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \PayPal\Model\PaypalOrder Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildOrder object.
     *
     * @param                  ChildOrder $v
     * @return                 \PayPal\Model\PaypalOrder The current object (for fluent API support)
     * @throws PropelException
     */
    public function setOrder(ChildOrder $v = null)
    {
        if ($v === null) {
            $this->setId(NULL);
        } else {
            $this->setId($v->getId());
        }

        $this->aOrder = $v;

        // Add binding for other direction of this 1:1 relationship.
        if ($v !== null) {
            $v->setPaypalOrder($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildOrder object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildOrder The associated ChildOrder object.
     * @throws PropelException
     */
    public function getOrder(ConnectionInterface $con = null)
    {
        if ($this->aOrder === null && ($this->id !== null)) {
            $this->aOrder = OrderQuery::create()->findPk($this->id, $con);
            // Because this foreign key represents a one-to-one relationship, we will create a bi-directional association.
            $this->aOrder->setPaypalOrder($this);
        }

        return $this->aOrder;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('PaypalPlan' == $relationName) {
            return $this->initPaypalPlans();
        }
        if ('PaypalOrderVersion' == $relationName) {
            return $this->initPaypalOrderVersions();
        }
    }

    /**
     * Clears out the collPaypalPlans collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addPaypalPlans()
     */
    public function clearPaypalPlans()
    {
        $this->collPaypalPlans = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collPaypalPlans collection loaded partially.
     */
    public function resetPartialPaypalPlans($v = true)
    {
        $this->collPaypalPlansPartial = $v;
    }

    /**
     * Initializes the collPaypalPlans collection.
     *
     * By default this just sets the collPaypalPlans collection to an empty array (like clearcollPaypalPlans());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPaypalPlans($overrideExisting = true)
    {
        if (null !== $this->collPaypalPlans && !$overrideExisting) {
            return;
        }
        $this->collPaypalPlans = new ObjectCollection();
        $this->collPaypalPlans->setModel('\PayPal\Model\PaypalPlan');
    }

    /**
     * Gets an array of ChildPaypalPlan objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPaypalOrder is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildPaypalPlan[] List of ChildPaypalPlan objects
     * @throws PropelException
     */
    public function getPaypalPlans($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collPaypalPlansPartial && !$this->isNew();
        if (null === $this->collPaypalPlans || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPaypalPlans) {
                // return empty collection
                $this->initPaypalPlans();
            } else {
                $collPaypalPlans = ChildPaypalPlanQuery::create(null, $criteria)
                    ->filterByPaypalOrder($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collPaypalPlansPartial && count($collPaypalPlans)) {
                        $this->initPaypalPlans(false);

                        foreach ($collPaypalPlans as $obj) {
                            if (false == $this->collPaypalPlans->contains($obj)) {
                                $this->collPaypalPlans->append($obj);
                            }
                        }

                        $this->collPaypalPlansPartial = true;
                    }

                    reset($collPaypalPlans);

                    return $collPaypalPlans;
                }

                if ($partial && $this->collPaypalPlans) {
                    foreach ($this->collPaypalPlans as $obj) {
                        if ($obj->isNew()) {
                            $collPaypalPlans[] = $obj;
                        }
                    }
                }

                $this->collPaypalPlans = $collPaypalPlans;
                $this->collPaypalPlansPartial = false;
            }
        }

        return $this->collPaypalPlans;
    }

    /**
     * Sets a collection of PaypalPlan objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $paypalPlans A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildPaypalOrder The current object (for fluent API support)
     */
    public function setPaypalPlans(Collection $paypalPlans, ConnectionInterface $con = null)
    {
        $paypalPlansToDelete = $this->getPaypalPlans(new Criteria(), $con)->diff($paypalPlans);


        $this->paypalPlansScheduledForDeletion = $paypalPlansToDelete;

        foreach ($paypalPlansToDelete as $paypalPlanRemoved) {
            $paypalPlanRemoved->setPaypalOrder(null);
        }

        $this->collPaypalPlans = null;
        foreach ($paypalPlans as $paypalPlan) {
            $this->addPaypalPlan($paypalPlan);
        }

        $this->collPaypalPlans = $paypalPlans;
        $this->collPaypalPlansPartial = false;

        return $this;
    }

    /**
     * Returns the number of related PaypalPlan objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related PaypalPlan objects.
     * @throws PropelException
     */
    public function countPaypalPlans(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collPaypalPlansPartial && !$this->isNew();
        if (null === $this->collPaypalPlans || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPaypalPlans) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getPaypalPlans());
            }

            $query = ChildPaypalPlanQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByPaypalOrder($this)
                ->count($con);
        }

        return count($this->collPaypalPlans);
    }

    /**
     * Method called to associate a ChildPaypalPlan object to this object
     * through the ChildPaypalPlan foreign key attribute.
     *
     * @param    ChildPaypalPlan $l ChildPaypalPlan
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function addPaypalPlan(ChildPaypalPlan $l)
    {
        if ($this->collPaypalPlans === null) {
            $this->initPaypalPlans();
            $this->collPaypalPlansPartial = true;
        }

        if (!in_array($l, $this->collPaypalPlans->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddPaypalPlan($l);
        }

        return $this;
    }

    /**
     * @param PaypalPlan $paypalPlan The paypalPlan object to add.
     */
    protected function doAddPaypalPlan($paypalPlan)
    {
        $this->collPaypalPlans[]= $paypalPlan;
        $paypalPlan->setPaypalOrder($this);
    }

    /**
     * @param  PaypalPlan $paypalPlan The paypalPlan object to remove.
     * @return ChildPaypalOrder The current object (for fluent API support)
     */
    public function removePaypalPlan($paypalPlan)
    {
        if ($this->getPaypalPlans()->contains($paypalPlan)) {
            $this->collPaypalPlans->remove($this->collPaypalPlans->search($paypalPlan));
            if (null === $this->paypalPlansScheduledForDeletion) {
                $this->paypalPlansScheduledForDeletion = clone $this->collPaypalPlans;
                $this->paypalPlansScheduledForDeletion->clear();
            }
            $this->paypalPlansScheduledForDeletion[]= clone $paypalPlan;
            $paypalPlan->setPaypalOrder(null);
        }

        return $this;
    }

    /**
     * Clears out the collPaypalOrderVersions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addPaypalOrderVersions()
     */
    public function clearPaypalOrderVersions()
    {
        $this->collPaypalOrderVersions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collPaypalOrderVersions collection loaded partially.
     */
    public function resetPartialPaypalOrderVersions($v = true)
    {
        $this->collPaypalOrderVersionsPartial = $v;
    }

    /**
     * Initializes the collPaypalOrderVersions collection.
     *
     * By default this just sets the collPaypalOrderVersions collection to an empty array (like clearcollPaypalOrderVersions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPaypalOrderVersions($overrideExisting = true)
    {
        if (null !== $this->collPaypalOrderVersions && !$overrideExisting) {
            return;
        }
        $this->collPaypalOrderVersions = new ObjectCollection();
        $this->collPaypalOrderVersions->setModel('\PayPal\Model\PaypalOrderVersion');
    }

    /**
     * Gets an array of ChildPaypalOrderVersion objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildPaypalOrder is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildPaypalOrderVersion[] List of ChildPaypalOrderVersion objects
     * @throws PropelException
     */
    public function getPaypalOrderVersions($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collPaypalOrderVersionsPartial && !$this->isNew();
        if (null === $this->collPaypalOrderVersions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPaypalOrderVersions) {
                // return empty collection
                $this->initPaypalOrderVersions();
            } else {
                $collPaypalOrderVersions = ChildPaypalOrderVersionQuery::create(null, $criteria)
                    ->filterByPaypalOrder($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collPaypalOrderVersionsPartial && count($collPaypalOrderVersions)) {
                        $this->initPaypalOrderVersions(false);

                        foreach ($collPaypalOrderVersions as $obj) {
                            if (false == $this->collPaypalOrderVersions->contains($obj)) {
                                $this->collPaypalOrderVersions->append($obj);
                            }
                        }

                        $this->collPaypalOrderVersionsPartial = true;
                    }

                    reset($collPaypalOrderVersions);

                    return $collPaypalOrderVersions;
                }

                if ($partial && $this->collPaypalOrderVersions) {
                    foreach ($this->collPaypalOrderVersions as $obj) {
                        if ($obj->isNew()) {
                            $collPaypalOrderVersions[] = $obj;
                        }
                    }
                }

                $this->collPaypalOrderVersions = $collPaypalOrderVersions;
                $this->collPaypalOrderVersionsPartial = false;
            }
        }

        return $this->collPaypalOrderVersions;
    }

    /**
     * Sets a collection of PaypalOrderVersion objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $paypalOrderVersions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildPaypalOrder The current object (for fluent API support)
     */
    public function setPaypalOrderVersions(Collection $paypalOrderVersions, ConnectionInterface $con = null)
    {
        $paypalOrderVersionsToDelete = $this->getPaypalOrderVersions(new Criteria(), $con)->diff($paypalOrderVersions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->paypalOrderVersionsScheduledForDeletion = clone $paypalOrderVersionsToDelete;

        foreach ($paypalOrderVersionsToDelete as $paypalOrderVersionRemoved) {
            $paypalOrderVersionRemoved->setPaypalOrder(null);
        }

        $this->collPaypalOrderVersions = null;
        foreach ($paypalOrderVersions as $paypalOrderVersion) {
            $this->addPaypalOrderVersion($paypalOrderVersion);
        }

        $this->collPaypalOrderVersions = $paypalOrderVersions;
        $this->collPaypalOrderVersionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related PaypalOrderVersion objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related PaypalOrderVersion objects.
     * @throws PropelException
     */
    public function countPaypalOrderVersions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collPaypalOrderVersionsPartial && !$this->isNew();
        if (null === $this->collPaypalOrderVersions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPaypalOrderVersions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getPaypalOrderVersions());
            }

            $query = ChildPaypalOrderVersionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByPaypalOrder($this)
                ->count($con);
        }

        return count($this->collPaypalOrderVersions);
    }

    /**
     * Method called to associate a ChildPaypalOrderVersion object to this object
     * through the ChildPaypalOrderVersion foreign key attribute.
     *
     * @param    ChildPaypalOrderVersion $l ChildPaypalOrderVersion
     * @return   \PayPal\Model\PaypalOrder The current object (for fluent API support)
     */
    public function addPaypalOrderVersion(ChildPaypalOrderVersion $l)
    {
        if ($this->collPaypalOrderVersions === null) {
            $this->initPaypalOrderVersions();
            $this->collPaypalOrderVersionsPartial = true;
        }

        if (!in_array($l, $this->collPaypalOrderVersions->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddPaypalOrderVersion($l);
        }

        return $this;
    }

    /**
     * @param PaypalOrderVersion $paypalOrderVersion The paypalOrderVersion object to add.
     */
    protected function doAddPaypalOrderVersion($paypalOrderVersion)
    {
        $this->collPaypalOrderVersions[]= $paypalOrderVersion;
        $paypalOrderVersion->setPaypalOrder($this);
    }

    /**
     * @param  PaypalOrderVersion $paypalOrderVersion The paypalOrderVersion object to remove.
     * @return ChildPaypalOrder The current object (for fluent API support)
     */
    public function removePaypalOrderVersion($paypalOrderVersion)
    {
        if ($this->getPaypalOrderVersions()->contains($paypalOrderVersion)) {
            $this->collPaypalOrderVersions->remove($this->collPaypalOrderVersions->search($paypalOrderVersion));
            if (null === $this->paypalOrderVersionsScheduledForDeletion) {
                $this->paypalOrderVersionsScheduledForDeletion = clone $this->collPaypalOrderVersions;
                $this->paypalOrderVersionsScheduledForDeletion->clear();
            }
            $this->paypalOrderVersionsScheduledForDeletion[]= clone $paypalOrderVersion;
            $paypalOrderVersion->setPaypalOrder(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->payment_id = null;
        $this->agreement_id = null;
        $this->credit_card_id = null;
        $this->state = null;
        $this->amount = null;
        $this->description = null;
        $this->payer_id = null;
        $this->token = null;
        $this->planified_title = null;
        $this->planified_description = null;
        $this->planified_frequency = null;
        $this->planified_frequency_interval = null;
        $this->planified_cycle = null;
        $this->planified_actual_cycle = null;
        $this->planified_min_amount = null;
        $this->planified_max_amount = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->version = null;
        $this->version_created_at = null;
        $this->version_created_by = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collPaypalPlans) {
                foreach ($this->collPaypalPlans as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPaypalOrderVersions) {
                foreach ($this->collPaypalOrderVersions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collPaypalPlans = null;
        $this->collPaypalOrderVersions = null;
        $this->aOrder = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(PaypalOrderTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildPaypalOrder The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[PaypalOrderTableMap::UPDATED_AT] = true;

        return $this;
    }

    // versionable behavior

    /**
     * Enforce a new Version of this object upon next save.
     *
     * @return \PayPal\Model\PaypalOrder
     */
    public function enforceVersioning()
    {
        $this->enforceVersion = true;

        return $this;
    }

    /**
     * Checks whether the current state must be recorded as a version
     *
     * @return  boolean
     */
    public function isVersioningNecessary($con = null)
    {
        if ($this->alreadyInSave) {
            return false;
        }

        if ($this->enforceVersion) {
            return true;
        }

        if (ChildPaypalOrderQuery::isVersioningEnabled() && ($this->isNew() || $this->isModified()) || $this->isDeleted()) {
            return true;
        }
        if (null !== ($object = $this->getOrder($con)) && $object->isVersioningNecessary($con)) {
            return true;
        }


        return false;
    }

    /**
     * Creates a version of the current object and saves it.
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildPaypalOrderVersion A version object
     */
    public function addVersion($con = null)
    {
        $this->enforceVersion = false;

        $version = new ChildPaypalOrderVersion();
        $version->setId($this->getId());
        $version->setPaymentId($this->getPaymentId());
        $version->setAgreementId($this->getAgreementId());
        $version->setCreditCardId($this->getCreditCardId());
        $version->setState($this->getState());
        $version->setAmount($this->getAmount());
        $version->setDescription($this->getDescription());
        $version->setPayerId($this->getPayerId());
        $version->setToken($this->getToken());
        $version->setPlanifiedTitle($this->getPlanifiedTitle());
        $version->setPlanifiedDescription($this->getPlanifiedDescription());
        $version->setPlanifiedFrequency($this->getPlanifiedFrequency());
        $version->setPlanifiedFrequencyInterval($this->getPlanifiedFrequencyInterval());
        $version->setPlanifiedCycle($this->getPlanifiedCycle());
        $version->setPlanifiedActualCycle($this->getPlanifiedActualCycle());
        $version->setPlanifiedMinAmount($this->getPlanifiedMinAmount());
        $version->setPlanifiedMaxAmount($this->getPlanifiedMaxAmount());
        $version->setCreatedAt($this->getCreatedAt());
        $version->setUpdatedAt($this->getUpdatedAt());
        $version->setVersion($this->getVersion());
        $version->setVersionCreatedAt($this->getVersionCreatedAt());
        $version->setVersionCreatedBy($this->getVersionCreatedBy());
        $version->setPaypalOrder($this);
        if (($related = $this->getOrder($con)) && $related->getVersion()) {
            $version->setIdVersion($related->getVersion());
        }
        $version->save($con);

        return $version;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con The connection to use
     *
     * @return  ChildPaypalOrder The current object (for fluent API support)
     */
    public function toVersion($versionNumber, $con = null)
    {
        $version = $this->getOneVersion($versionNumber, $con);
        if (!$version) {
            throw new PropelException(sprintf('No ChildPaypalOrder object found with version %d', $version));
        }
        $this->populateFromVersion($version, $con);

        return $this;
    }

    /**
     * Sets the properties of the current object to the value they had at a specific version
     *
     * @param ChildPaypalOrderVersion $version The version object to use
     * @param ConnectionInterface   $con the connection to use
     * @param array                 $loadedObjects objects that been loaded in a chain of populateFromVersion calls on referrer or fk objects.
     *
     * @return ChildPaypalOrder The current object (for fluent API support)
     */
    public function populateFromVersion($version, $con = null, &$loadedObjects = array())
    {
        $loadedObjects['ChildPaypalOrder'][$version->getId()][$version->getVersion()] = $this;
        $this->setId($version->getId());
        $this->setPaymentId($version->getPaymentId());
        $this->setAgreementId($version->getAgreementId());
        $this->setCreditCardId($version->getCreditCardId());
        $this->setState($version->getState());
        $this->setAmount($version->getAmount());
        $this->setDescription($version->getDescription());
        $this->setPayerId($version->getPayerId());
        $this->setToken($version->getToken());
        $this->setPlanifiedTitle($version->getPlanifiedTitle());
        $this->setPlanifiedDescription($version->getPlanifiedDescription());
        $this->setPlanifiedFrequency($version->getPlanifiedFrequency());
        $this->setPlanifiedFrequencyInterval($version->getPlanifiedFrequencyInterval());
        $this->setPlanifiedCycle($version->getPlanifiedCycle());
        $this->setPlanifiedActualCycle($version->getPlanifiedActualCycle());
        $this->setPlanifiedMinAmount($version->getPlanifiedMinAmount());
        $this->setPlanifiedMaxAmount($version->getPlanifiedMaxAmount());
        $this->setCreatedAt($version->getCreatedAt());
        $this->setUpdatedAt($version->getUpdatedAt());
        $this->setVersion($version->getVersion());
        $this->setVersionCreatedAt($version->getVersionCreatedAt());
        $this->setVersionCreatedBy($version->getVersionCreatedBy());
        if ($fkValue = $version->getId()) {
            if (isset($loadedObjects['ChildOrder']) && isset($loadedObjects['ChildOrder'][$fkValue]) && isset($loadedObjects['ChildOrder'][$fkValue][$version->getIdVersion()])) {
                $related = $loadedObjects['ChildOrder'][$fkValue][$version->getIdVersion()];
            } else {
                $related = new ChildOrder();
                $relatedVersion = OrderVersionQuery::create()
                    ->filterById($fkValue)
                    ->filterByVersion($version->getIdVersion())
                    ->findOne($con);
                $related->populateFromVersion($relatedVersion, $con, $loadedObjects);
                $related->setNew(false);
            }
            $this->setOrder($related);
        }

        return $this;
    }

    /**
     * Gets the latest persisted version number for the current object
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  integer
     */
    public function getLastVersionNumber($con = null)
    {
        $v = ChildPaypalOrderVersionQuery::create()
            ->filterByPaypalOrder($this)
            ->orderByVersion('desc')
            ->findOne($con);
        if (!$v) {
            return 0;
        }

        return $v->getVersion();
    }

    /**
     * Checks whether the current object is the latest one
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  Boolean
     */
    public function isLastVersion($con = null)
    {
        return $this->getLastVersionNumber($con) == $this->getVersion();
    }

    /**
     * Retrieves a version object for this entity and a version number
     *
     * @param   integer $versionNumber The version number to read
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ChildPaypalOrderVersion A version object
     */
    public function getOneVersion($versionNumber, $con = null)
    {
        return ChildPaypalOrderVersionQuery::create()
            ->filterByPaypalOrder($this)
            ->filterByVersion($versionNumber)
            ->findOne($con);
    }

    /**
     * Gets all the versions of this object, in incremental order
     *
     * @param   ConnectionInterface $con the connection to use
     *
     * @return  ObjectCollection A list of ChildPaypalOrderVersion objects
     */
    public function getAllVersions($con = null)
    {
        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn(PaypalOrderVersionTableMap::VERSION);

        return $this->getPaypalOrderVersions($criteria, $con);
    }

    /**
     * Compares the current object with another of its version.
     * <code>
     * print_r($book->compareVersion(1));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $versionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersion($versionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->toArray();
        $toVersion = $this->getOneVersion($versionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Compares two versions of the current object.
     * <code>
     * print_r($book->compareVersions(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   integer             $fromVersionNumber
     * @param   integer             $toVersionNumber
     * @param   string              $keys Main key used for the result diff (versions|columns)
     * @param   ConnectionInterface $con the connection to use
     * @param   array               $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    public function compareVersions($fromVersionNumber, $toVersionNumber, $keys = 'columns', $con = null, $ignoredColumns = array())
    {
        $fromVersion = $this->getOneVersion($fromVersionNumber, $con)->toArray();
        $toVersion = $this->getOneVersion($toVersionNumber, $con)->toArray();

        return $this->computeDiff($fromVersion, $toVersion, $keys, $ignoredColumns);
    }

    /**
     * Computes the diff between two versions.
     * <code>
     * print_r($book->computeDiff(1, 2));
     * => array(
     *   '1' => array('Title' => 'Book title at version 1'),
     *   '2' => array('Title' => 'Book title at version 2')
     * );
     * </code>
     *
     * @param   array     $fromVersion     An array representing the original version.
     * @param   array     $toVersion       An array representing the destination version.
     * @param   string    $keys            Main key used for the result diff (versions|columns).
     * @param   array     $ignoredColumns  The columns to exclude from the diff.
     *
     * @return  array A list of differences
     */
    protected function computeDiff($fromVersion, $toVersion, $keys = 'columns', $ignoredColumns = array())
    {
        $fromVersionNumber = $fromVersion['Version'];
        $toVersionNumber = $toVersion['Version'];
        $ignoredColumns = array_merge(array(
            'Version',
            'VersionCreatedAt',
            'VersionCreatedBy',
        ), $ignoredColumns);
        $diff = array();
        foreach ($fromVersion as $key => $value) {
            if (in_array($key, $ignoredColumns)) {
                continue;
            }
            if ($toVersion[$key] != $value) {
                switch ($keys) {
                    case 'versions':
                        $diff[$fromVersionNumber][$key] = $value;
                        $diff[$toVersionNumber][$key] = $toVersion[$key];
                        break;
                    default:
                        $diff[$key] = array(
                            $fromVersionNumber => $value,
                            $toVersionNumber => $toVersion[$key],
                        );
                        break;
                }
            }
        }

        return $diff;
    }
    /**
     * retrieve the last $number versions.
     *
     * @param Integer $number the number of record to return.
     * @return PropelCollection|array \PayPal\Model\PaypalOrderVersion[] List of \PayPal\Model\PaypalOrderVersion objects
     */
    public function getLastVersions($number = 10, $criteria = null, $con = null)
    {
        $criteria = ChildPaypalOrderVersionQuery::create(null, $criteria);
        $criteria->addDescendingOrderByColumn(PaypalOrderVersionTableMap::VERSION);
        $criteria->limit($number);

        return $this->getPaypalOrderVersions($criteria, $con);
    }
    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
