<?php

namespace PayPal\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use PayPal\Model\PaypalCustomer as ChildPaypalCustomer;
use PayPal\Model\PaypalCustomerQuery as ChildPaypalCustomerQuery;
use PayPal\Model\Map\PaypalCustomerTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Thelia\Model\Customer as ChildCustomer;
use Thelia\Model\CustomerQuery;

abstract class PaypalCustomer implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\PayPal\\Model\\Map\\PaypalCustomerTableMap';


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
     * The value for the paypal_user_id field.
     * @var        int
     */
    protected $paypal_user_id;

    /**
     * The value for the credit_card_id field.
     * @var        string
     */
    protected $credit_card_id;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * The value for the given_name field.
     * @var        string
     */
    protected $given_name;

    /**
     * The value for the family_name field.
     * @var        string
     */
    protected $family_name;

    /**
     * The value for the middle_name field.
     * @var        string
     */
    protected $middle_name;

    /**
     * The value for the picture field.
     * @var        string
     */
    protected $picture;

    /**
     * The value for the email_verified field.
     * @var        int
     */
    protected $email_verified;

    /**
     * The value for the gender field.
     * @var        string
     */
    protected $gender;

    /**
     * The value for the birthday field.
     * @var        string
     */
    protected $birthday;

    /**
     * The value for the zoneinfo field.
     * @var        string
     */
    protected $zoneinfo;

    /**
     * The value for the locale field.
     * @var        string
     */
    protected $locale;

    /**
     * The value for the language field.
     * @var        string
     */
    protected $language;

    /**
     * The value for the verified field.
     * @var        int
     */
    protected $verified;

    /**
     * The value for the phone_number field.
     * @var        string
     */
    protected $phone_number;

    /**
     * The value for the verified_account field.
     * @var        string
     */
    protected $verified_account;

    /**
     * The value for the account_type field.
     * @var        string
     */
    protected $account_type;

    /**
     * The value for the age_range field.
     * @var        string
     */
    protected $age_range;

    /**
     * The value for the payer_id field.
     * @var        string
     */
    protected $payer_id;

    /**
     * The value for the postal_code field.
     * @var        string
     */
    protected $postal_code;

    /**
     * The value for the locality field.
     * @var        string
     */
    protected $locality;

    /**
     * The value for the region field.
     * @var        string
     */
    protected $region;

    /**
     * The value for the country field.
     * @var        string
     */
    protected $country;

    /**
     * The value for the street_address field.
     * @var        string
     */
    protected $street_address;

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
     * @var        Customer
     */
    protected $aCustomer;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * Initializes internal state of PayPal\Model\Base\PaypalCustomer object.
     */
    public function __construct()
    {
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
     * Compares this with another <code>PaypalCustomer</code> instance.  If
     * <code>obj</code> is an instance of <code>PaypalCustomer</code>, delegates to
     * <code>equals(PaypalCustomer)</code>.  Otherwise, returns <code>false</code>.
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
     * @return PaypalCustomer The current object, for fluid interface
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
     * @return PaypalCustomer The current object, for fluid interface
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
     * Get the [paypal_user_id] column value.
     *
     * @return   int
     */
    public function getPaypalUserId()
    {

        return $this->paypal_user_id;
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
     * Get the [name] column value.
     *
     * @return   string
     */
    public function getName()
    {

        return $this->name;
    }

    /**
     * Get the [given_name] column value.
     *
     * @return   string
     */
    public function getGivenName()
    {

        return $this->given_name;
    }

    /**
     * Get the [family_name] column value.
     *
     * @return   string
     */
    public function getFamilyName()
    {

        return $this->family_name;
    }

    /**
     * Get the [middle_name] column value.
     *
     * @return   string
     */
    public function getMiddleName()
    {

        return $this->middle_name;
    }

    /**
     * Get the [picture] column value.
     *
     * @return   string
     */
    public function getPicture()
    {

        return $this->picture;
    }

    /**
     * Get the [email_verified] column value.
     *
     * @return   int
     */
    public function getEmailVerified()
    {

        return $this->email_verified;
    }

    /**
     * Get the [gender] column value.
     *
     * @return   string
     */
    public function getGender()
    {

        return $this->gender;
    }

    /**
     * Get the [birthday] column value.
     *
     * @return   string
     */
    public function getBirthday()
    {

        return $this->birthday;
    }

    /**
     * Get the [zoneinfo] column value.
     *
     * @return   string
     */
    public function getZoneinfo()
    {

        return $this->zoneinfo;
    }

    /**
     * Get the [locale] column value.
     *
     * @return   string
     */
    public function getLocale()
    {

        return $this->locale;
    }

    /**
     * Get the [language] column value.
     *
     * @return   string
     */
    public function getLanguage()
    {

        return $this->language;
    }

    /**
     * Get the [verified] column value.
     *
     * @return   int
     */
    public function getVerified()
    {

        return $this->verified;
    }

    /**
     * Get the [phone_number] column value.
     *
     * @return   string
     */
    public function getPhoneNumber()
    {

        return $this->phone_number;
    }

    /**
     * Get the [verified_account] column value.
     *
     * @return   string
     */
    public function getVerifiedAccount()
    {

        return $this->verified_account;
    }

    /**
     * Get the [account_type] column value.
     *
     * @return   string
     */
    public function getAccountType()
    {

        return $this->account_type;
    }

    /**
     * Get the [age_range] column value.
     *
     * @return   string
     */
    public function getAgeRange()
    {

        return $this->age_range;
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
     * Get the [postal_code] column value.
     *
     * @return   string
     */
    public function getPostalCode()
    {

        return $this->postal_code;
    }

    /**
     * Get the [locality] column value.
     *
     * @return   string
     */
    public function getLocality()
    {

        return $this->locality;
    }

    /**
     * Get the [region] column value.
     *
     * @return   string
     */
    public function getRegion()
    {

        return $this->region;
    }

    /**
     * Get the [country] column value.
     *
     * @return   string
     */
    public function getCountry()
    {

        return $this->country;
    }

    /**
     * Get the [street_address] column value.
     *
     * @return   string
     */
    public function getStreetAddress()
    {

        return $this->street_address;
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
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::ID] = true;
        }

        if ($this->aCustomer !== null && $this->aCustomer->getId() !== $v) {
            $this->aCustomer = null;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [paypal_user_id] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setPaypalUserId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->paypal_user_id !== $v) {
            $this->paypal_user_id = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::PAYPAL_USER_ID] = true;
        }


        return $this;
    } // setPaypalUserId()

    /**
     * Set the value of [credit_card_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setCreditCardId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->credit_card_id !== $v) {
            $this->credit_card_id = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::CREDIT_CARD_ID] = true;
        }


        return $this;
    } // setCreditCardId()

    /**
     * Set the value of [name] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::NAME] = true;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [given_name] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setGivenName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->given_name !== $v) {
            $this->given_name = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::GIVEN_NAME] = true;
        }


        return $this;
    } // setGivenName()

    /**
     * Set the value of [family_name] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setFamilyName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->family_name !== $v) {
            $this->family_name = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::FAMILY_NAME] = true;
        }


        return $this;
    } // setFamilyName()

    /**
     * Set the value of [middle_name] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setMiddleName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->middle_name !== $v) {
            $this->middle_name = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::MIDDLE_NAME] = true;
        }


        return $this;
    } // setMiddleName()

    /**
     * Set the value of [picture] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setPicture($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->picture !== $v) {
            $this->picture = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::PICTURE] = true;
        }


        return $this;
    } // setPicture()

    /**
     * Set the value of [email_verified] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setEmailVerified($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->email_verified !== $v) {
            $this->email_verified = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::EMAIL_VERIFIED] = true;
        }


        return $this;
    } // setEmailVerified()

    /**
     * Set the value of [gender] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setGender($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->gender !== $v) {
            $this->gender = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::GENDER] = true;
        }


        return $this;
    } // setGender()

    /**
     * Set the value of [birthday] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setBirthday($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->birthday !== $v) {
            $this->birthday = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::BIRTHDAY] = true;
        }


        return $this;
    } // setBirthday()

    /**
     * Set the value of [zoneinfo] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setZoneinfo($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->zoneinfo !== $v) {
            $this->zoneinfo = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::ZONEINFO] = true;
        }


        return $this;
    } // setZoneinfo()

    /**
     * Set the value of [locale] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setLocale($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->locale !== $v) {
            $this->locale = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::LOCALE] = true;
        }


        return $this;
    } // setLocale()

    /**
     * Set the value of [language] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setLanguage($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->language !== $v) {
            $this->language = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::LANGUAGE] = true;
        }


        return $this;
    } // setLanguage()

    /**
     * Set the value of [verified] column.
     *
     * @param      int $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setVerified($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->verified !== $v) {
            $this->verified = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::VERIFIED] = true;
        }


        return $this;
    } // setVerified()

    /**
     * Set the value of [phone_number] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setPhoneNumber($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->phone_number !== $v) {
            $this->phone_number = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::PHONE_NUMBER] = true;
        }


        return $this;
    } // setPhoneNumber()

    /**
     * Set the value of [verified_account] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setVerifiedAccount($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->verified_account !== $v) {
            $this->verified_account = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::VERIFIED_ACCOUNT] = true;
        }


        return $this;
    } // setVerifiedAccount()

    /**
     * Set the value of [account_type] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setAccountType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->account_type !== $v) {
            $this->account_type = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::ACCOUNT_TYPE] = true;
        }


        return $this;
    } // setAccountType()

    /**
     * Set the value of [age_range] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setAgeRange($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->age_range !== $v) {
            $this->age_range = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::AGE_RANGE] = true;
        }


        return $this;
    } // setAgeRange()

    /**
     * Set the value of [payer_id] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setPayerId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->payer_id !== $v) {
            $this->payer_id = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::PAYER_ID] = true;
        }


        return $this;
    } // setPayerId()

    /**
     * Set the value of [postal_code] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setPostalCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->postal_code !== $v) {
            $this->postal_code = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::POSTAL_CODE] = true;
        }


        return $this;
    } // setPostalCode()

    /**
     * Set the value of [locality] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setLocality($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->locality !== $v) {
            $this->locality = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::LOCALITY] = true;
        }


        return $this;
    } // setLocality()

    /**
     * Set the value of [region] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setRegion($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->region !== $v) {
            $this->region = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::REGION] = true;
        }


        return $this;
    } // setRegion()

    /**
     * Set the value of [country] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setCountry($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->country !== $v) {
            $this->country = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::COUNTRY] = true;
        }


        return $this;
    } // setCountry()

    /**
     * Set the value of [street_address] column.
     *
     * @param      string $v new value
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setStreetAddress($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->street_address !== $v) {
            $this->street_address = $v;
            $this->modifiedColumns[PaypalCustomerTableMap::STREET_ADDRESS] = true;
        }


        return $this;
    } // setStreetAddress()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[PaypalCustomerTableMap::CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[PaypalCustomerTableMap::UPDATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

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


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : PaypalCustomerTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : PaypalCustomerTableMap::translateFieldName('PaypalUserId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->paypal_user_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : PaypalCustomerTableMap::translateFieldName('CreditCardId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->credit_card_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : PaypalCustomerTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : PaypalCustomerTableMap::translateFieldName('GivenName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->given_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : PaypalCustomerTableMap::translateFieldName('FamilyName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->family_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : PaypalCustomerTableMap::translateFieldName('MiddleName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->middle_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : PaypalCustomerTableMap::translateFieldName('Picture', TableMap::TYPE_PHPNAME, $indexType)];
            $this->picture = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : PaypalCustomerTableMap::translateFieldName('EmailVerified', TableMap::TYPE_PHPNAME, $indexType)];
            $this->email_verified = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : PaypalCustomerTableMap::translateFieldName('Gender', TableMap::TYPE_PHPNAME, $indexType)];
            $this->gender = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : PaypalCustomerTableMap::translateFieldName('Birthday', TableMap::TYPE_PHPNAME, $indexType)];
            $this->birthday = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : PaypalCustomerTableMap::translateFieldName('Zoneinfo', TableMap::TYPE_PHPNAME, $indexType)];
            $this->zoneinfo = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : PaypalCustomerTableMap::translateFieldName('Locale', TableMap::TYPE_PHPNAME, $indexType)];
            $this->locale = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 13 + $startcol : PaypalCustomerTableMap::translateFieldName('Language', TableMap::TYPE_PHPNAME, $indexType)];
            $this->language = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 14 + $startcol : PaypalCustomerTableMap::translateFieldName('Verified', TableMap::TYPE_PHPNAME, $indexType)];
            $this->verified = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 15 + $startcol : PaypalCustomerTableMap::translateFieldName('PhoneNumber', TableMap::TYPE_PHPNAME, $indexType)];
            $this->phone_number = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 16 + $startcol : PaypalCustomerTableMap::translateFieldName('VerifiedAccount', TableMap::TYPE_PHPNAME, $indexType)];
            $this->verified_account = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 17 + $startcol : PaypalCustomerTableMap::translateFieldName('AccountType', TableMap::TYPE_PHPNAME, $indexType)];
            $this->account_type = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 18 + $startcol : PaypalCustomerTableMap::translateFieldName('AgeRange', TableMap::TYPE_PHPNAME, $indexType)];
            $this->age_range = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 19 + $startcol : PaypalCustomerTableMap::translateFieldName('PayerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->payer_id = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 20 + $startcol : PaypalCustomerTableMap::translateFieldName('PostalCode', TableMap::TYPE_PHPNAME, $indexType)];
            $this->postal_code = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 21 + $startcol : PaypalCustomerTableMap::translateFieldName('Locality', TableMap::TYPE_PHPNAME, $indexType)];
            $this->locality = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 22 + $startcol : PaypalCustomerTableMap::translateFieldName('Region', TableMap::TYPE_PHPNAME, $indexType)];
            $this->region = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 23 + $startcol : PaypalCustomerTableMap::translateFieldName('Country', TableMap::TYPE_PHPNAME, $indexType)];
            $this->country = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 24 + $startcol : PaypalCustomerTableMap::translateFieldName('StreetAddress', TableMap::TYPE_PHPNAME, $indexType)];
            $this->street_address = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 25 + $startcol : PaypalCustomerTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 26 + $startcol : PaypalCustomerTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 27; // 27 = PaypalCustomerTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \PayPal\Model\PaypalCustomer object", 0, $e);
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
        if ($this->aCustomer !== null && $this->id !== $this->aCustomer->getId()) {
            $this->aCustomer = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildPaypalCustomerQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aCustomer = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see PaypalCustomer::setDeleted()
     * @see PaypalCustomer::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildPaypalCustomerQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(PaypalCustomerTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(PaypalCustomerTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(PaypalCustomerTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(PaypalCustomerTableMap::UPDATED_AT)) {
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
                PaypalCustomerTableMap::addInstanceToPool($this);
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

            if ($this->aCustomer !== null) {
                if ($this->aCustomer->isModified() || $this->aCustomer->isNew()) {
                    $affectedRows += $this->aCustomer->save($con);
                }
                $this->setCustomer($this->aCustomer);
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
        if ($this->isColumnModified(PaypalCustomerTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::PAYPAL_USER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'PAYPAL_USER_ID';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::CREDIT_CARD_ID)) {
            $modifiedColumns[':p' . $index++]  = 'CREDIT_CARD_ID';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::NAME)) {
            $modifiedColumns[':p' . $index++]  = 'NAME';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::GIVEN_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'GIVEN_NAME';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::FAMILY_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'FAMILY_NAME';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::MIDDLE_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'MIDDLE_NAME';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::PICTURE)) {
            $modifiedColumns[':p' . $index++]  = 'PICTURE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::EMAIL_VERIFIED)) {
            $modifiedColumns[':p' . $index++]  = 'EMAIL_VERIFIED';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::GENDER)) {
            $modifiedColumns[':p' . $index++]  = 'GENDER';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::BIRTHDAY)) {
            $modifiedColumns[':p' . $index++]  = 'BIRTHDAY';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::ZONEINFO)) {
            $modifiedColumns[':p' . $index++]  = 'ZONEINFO';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::LOCALE)) {
            $modifiedColumns[':p' . $index++]  = 'LOCALE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::LANGUAGE)) {
            $modifiedColumns[':p' . $index++]  = 'LANGUAGE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::VERIFIED)) {
            $modifiedColumns[':p' . $index++]  = 'VERIFIED';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::PHONE_NUMBER)) {
            $modifiedColumns[':p' . $index++]  = 'PHONE_NUMBER';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::VERIFIED_ACCOUNT)) {
            $modifiedColumns[':p' . $index++]  = 'VERIFIED_ACCOUNT';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::ACCOUNT_TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'ACCOUNT_TYPE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::AGE_RANGE)) {
            $modifiedColumns[':p' . $index++]  = 'AGE_RANGE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::PAYER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'PAYER_ID';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::POSTAL_CODE)) {
            $modifiedColumns[':p' . $index++]  = 'POSTAL_CODE';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::LOCALITY)) {
            $modifiedColumns[':p' . $index++]  = 'LOCALITY';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::REGION)) {
            $modifiedColumns[':p' . $index++]  = 'REGION';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::COUNTRY)) {
            $modifiedColumns[':p' . $index++]  = 'COUNTRY';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::STREET_ADDRESS)) {
            $modifiedColumns[':p' . $index++]  = 'STREET_ADDRESS';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(PaypalCustomerTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }

        $sql = sprintf(
            'INSERT INTO paypal_customer (%s) VALUES (%s)',
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
                    case 'PAYPAL_USER_ID':
                        $stmt->bindValue($identifier, $this->paypal_user_id, PDO::PARAM_INT);
                        break;
                    case 'CREDIT_CARD_ID':
                        $stmt->bindValue($identifier, $this->credit_card_id, PDO::PARAM_STR);
                        break;
                    case 'NAME':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case 'GIVEN_NAME':
                        $stmt->bindValue($identifier, $this->given_name, PDO::PARAM_STR);
                        break;
                    case 'FAMILY_NAME':
                        $stmt->bindValue($identifier, $this->family_name, PDO::PARAM_STR);
                        break;
                    case 'MIDDLE_NAME':
                        $stmt->bindValue($identifier, $this->middle_name, PDO::PARAM_STR);
                        break;
                    case 'PICTURE':
                        $stmt->bindValue($identifier, $this->picture, PDO::PARAM_STR);
                        break;
                    case 'EMAIL_VERIFIED':
                        $stmt->bindValue($identifier, $this->email_verified, PDO::PARAM_INT);
                        break;
                    case 'GENDER':
                        $stmt->bindValue($identifier, $this->gender, PDO::PARAM_STR);
                        break;
                    case 'BIRTHDAY':
                        $stmt->bindValue($identifier, $this->birthday, PDO::PARAM_STR);
                        break;
                    case 'ZONEINFO':
                        $stmt->bindValue($identifier, $this->zoneinfo, PDO::PARAM_STR);
                        break;
                    case 'LOCALE':
                        $stmt->bindValue($identifier, $this->locale, PDO::PARAM_STR);
                        break;
                    case 'LANGUAGE':
                        $stmt->bindValue($identifier, $this->language, PDO::PARAM_STR);
                        break;
                    case 'VERIFIED':
                        $stmt->bindValue($identifier, $this->verified, PDO::PARAM_INT);
                        break;
                    case 'PHONE_NUMBER':
                        $stmt->bindValue($identifier, $this->phone_number, PDO::PARAM_STR);
                        break;
                    case 'VERIFIED_ACCOUNT':
                        $stmt->bindValue($identifier, $this->verified_account, PDO::PARAM_STR);
                        break;
                    case 'ACCOUNT_TYPE':
                        $stmt->bindValue($identifier, $this->account_type, PDO::PARAM_STR);
                        break;
                    case 'AGE_RANGE':
                        $stmt->bindValue($identifier, $this->age_range, PDO::PARAM_STR);
                        break;
                    case 'PAYER_ID':
                        $stmt->bindValue($identifier, $this->payer_id, PDO::PARAM_STR);
                        break;
                    case 'POSTAL_CODE':
                        $stmt->bindValue($identifier, $this->postal_code, PDO::PARAM_STR);
                        break;
                    case 'LOCALITY':
                        $stmt->bindValue($identifier, $this->locality, PDO::PARAM_STR);
                        break;
                    case 'REGION':
                        $stmt->bindValue($identifier, $this->region, PDO::PARAM_STR);
                        break;
                    case 'COUNTRY':
                        $stmt->bindValue($identifier, $this->country, PDO::PARAM_STR);
                        break;
                    case 'STREET_ADDRESS':
                        $stmt->bindValue($identifier, $this->street_address, PDO::PARAM_STR);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
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
        $pos = PaypalCustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getPaypalUserId();
                break;
            case 2:
                return $this->getCreditCardId();
                break;
            case 3:
                return $this->getName();
                break;
            case 4:
                return $this->getGivenName();
                break;
            case 5:
                return $this->getFamilyName();
                break;
            case 6:
                return $this->getMiddleName();
                break;
            case 7:
                return $this->getPicture();
                break;
            case 8:
                return $this->getEmailVerified();
                break;
            case 9:
                return $this->getGender();
                break;
            case 10:
                return $this->getBirthday();
                break;
            case 11:
                return $this->getZoneinfo();
                break;
            case 12:
                return $this->getLocale();
                break;
            case 13:
                return $this->getLanguage();
                break;
            case 14:
                return $this->getVerified();
                break;
            case 15:
                return $this->getPhoneNumber();
                break;
            case 16:
                return $this->getVerifiedAccount();
                break;
            case 17:
                return $this->getAccountType();
                break;
            case 18:
                return $this->getAgeRange();
                break;
            case 19:
                return $this->getPayerId();
                break;
            case 20:
                return $this->getPostalCode();
                break;
            case 21:
                return $this->getLocality();
                break;
            case 22:
                return $this->getRegion();
                break;
            case 23:
                return $this->getCountry();
                break;
            case 24:
                return $this->getStreetAddress();
                break;
            case 25:
                return $this->getCreatedAt();
                break;
            case 26:
                return $this->getUpdatedAt();
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
        if (isset($alreadyDumpedObjects['PaypalCustomer'][serialize($this->getPrimaryKey())])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['PaypalCustomer'][serialize($this->getPrimaryKey())] = true;
        $keys = PaypalCustomerTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getPaypalUserId(),
            $keys[2] => $this->getCreditCardId(),
            $keys[3] => $this->getName(),
            $keys[4] => $this->getGivenName(),
            $keys[5] => $this->getFamilyName(),
            $keys[6] => $this->getMiddleName(),
            $keys[7] => $this->getPicture(),
            $keys[8] => $this->getEmailVerified(),
            $keys[9] => $this->getGender(),
            $keys[10] => $this->getBirthday(),
            $keys[11] => $this->getZoneinfo(),
            $keys[12] => $this->getLocale(),
            $keys[13] => $this->getLanguage(),
            $keys[14] => $this->getVerified(),
            $keys[15] => $this->getPhoneNumber(),
            $keys[16] => $this->getVerifiedAccount(),
            $keys[17] => $this->getAccountType(),
            $keys[18] => $this->getAgeRange(),
            $keys[19] => $this->getPayerId(),
            $keys[20] => $this->getPostalCode(),
            $keys[21] => $this->getLocality(),
            $keys[22] => $this->getRegion(),
            $keys[23] => $this->getCountry(),
            $keys[24] => $this->getStreetAddress(),
            $keys[25] => $this->getCreatedAt(),
            $keys[26] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aCustomer) {
                $result['Customer'] = $this->aCustomer->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
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
        $pos = PaypalCustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setPaypalUserId($value);
                break;
            case 2:
                $this->setCreditCardId($value);
                break;
            case 3:
                $this->setName($value);
                break;
            case 4:
                $this->setGivenName($value);
                break;
            case 5:
                $this->setFamilyName($value);
                break;
            case 6:
                $this->setMiddleName($value);
                break;
            case 7:
                $this->setPicture($value);
                break;
            case 8:
                $this->setEmailVerified($value);
                break;
            case 9:
                $this->setGender($value);
                break;
            case 10:
                $this->setBirthday($value);
                break;
            case 11:
                $this->setZoneinfo($value);
                break;
            case 12:
                $this->setLocale($value);
                break;
            case 13:
                $this->setLanguage($value);
                break;
            case 14:
                $this->setVerified($value);
                break;
            case 15:
                $this->setPhoneNumber($value);
                break;
            case 16:
                $this->setVerifiedAccount($value);
                break;
            case 17:
                $this->setAccountType($value);
                break;
            case 18:
                $this->setAgeRange($value);
                break;
            case 19:
                $this->setPayerId($value);
                break;
            case 20:
                $this->setPostalCode($value);
                break;
            case 21:
                $this->setLocality($value);
                break;
            case 22:
                $this->setRegion($value);
                break;
            case 23:
                $this->setCountry($value);
                break;
            case 24:
                $this->setStreetAddress($value);
                break;
            case 25:
                $this->setCreatedAt($value);
                break;
            case 26:
                $this->setUpdatedAt($value);
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
        $keys = PaypalCustomerTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setPaypalUserId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setCreditCardId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setName($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setGivenName($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setFamilyName($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setMiddleName($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setPicture($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setEmailVerified($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setGender($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setBirthday($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setZoneinfo($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setLocale($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setLanguage($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setVerified($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setPhoneNumber($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setVerifiedAccount($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setAccountType($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setAgeRange($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setPayerId($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setPostalCode($arr[$keys[20]]);
        if (array_key_exists($keys[21], $arr)) $this->setLocality($arr[$keys[21]]);
        if (array_key_exists($keys[22], $arr)) $this->setRegion($arr[$keys[22]]);
        if (array_key_exists($keys[23], $arr)) $this->setCountry($arr[$keys[23]]);
        if (array_key_exists($keys[24], $arr)) $this->setStreetAddress($arr[$keys[24]]);
        if (array_key_exists($keys[25], $arr)) $this->setCreatedAt($arr[$keys[25]]);
        if (array_key_exists($keys[26], $arr)) $this->setUpdatedAt($arr[$keys[26]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(PaypalCustomerTableMap::DATABASE_NAME);

        if ($this->isColumnModified(PaypalCustomerTableMap::ID)) $criteria->add(PaypalCustomerTableMap::ID, $this->id);
        if ($this->isColumnModified(PaypalCustomerTableMap::PAYPAL_USER_ID)) $criteria->add(PaypalCustomerTableMap::PAYPAL_USER_ID, $this->paypal_user_id);
        if ($this->isColumnModified(PaypalCustomerTableMap::CREDIT_CARD_ID)) $criteria->add(PaypalCustomerTableMap::CREDIT_CARD_ID, $this->credit_card_id);
        if ($this->isColumnModified(PaypalCustomerTableMap::NAME)) $criteria->add(PaypalCustomerTableMap::NAME, $this->name);
        if ($this->isColumnModified(PaypalCustomerTableMap::GIVEN_NAME)) $criteria->add(PaypalCustomerTableMap::GIVEN_NAME, $this->given_name);
        if ($this->isColumnModified(PaypalCustomerTableMap::FAMILY_NAME)) $criteria->add(PaypalCustomerTableMap::FAMILY_NAME, $this->family_name);
        if ($this->isColumnModified(PaypalCustomerTableMap::MIDDLE_NAME)) $criteria->add(PaypalCustomerTableMap::MIDDLE_NAME, $this->middle_name);
        if ($this->isColumnModified(PaypalCustomerTableMap::PICTURE)) $criteria->add(PaypalCustomerTableMap::PICTURE, $this->picture);
        if ($this->isColumnModified(PaypalCustomerTableMap::EMAIL_VERIFIED)) $criteria->add(PaypalCustomerTableMap::EMAIL_VERIFIED, $this->email_verified);
        if ($this->isColumnModified(PaypalCustomerTableMap::GENDER)) $criteria->add(PaypalCustomerTableMap::GENDER, $this->gender);
        if ($this->isColumnModified(PaypalCustomerTableMap::BIRTHDAY)) $criteria->add(PaypalCustomerTableMap::BIRTHDAY, $this->birthday);
        if ($this->isColumnModified(PaypalCustomerTableMap::ZONEINFO)) $criteria->add(PaypalCustomerTableMap::ZONEINFO, $this->zoneinfo);
        if ($this->isColumnModified(PaypalCustomerTableMap::LOCALE)) $criteria->add(PaypalCustomerTableMap::LOCALE, $this->locale);
        if ($this->isColumnModified(PaypalCustomerTableMap::LANGUAGE)) $criteria->add(PaypalCustomerTableMap::LANGUAGE, $this->language);
        if ($this->isColumnModified(PaypalCustomerTableMap::VERIFIED)) $criteria->add(PaypalCustomerTableMap::VERIFIED, $this->verified);
        if ($this->isColumnModified(PaypalCustomerTableMap::PHONE_NUMBER)) $criteria->add(PaypalCustomerTableMap::PHONE_NUMBER, $this->phone_number);
        if ($this->isColumnModified(PaypalCustomerTableMap::VERIFIED_ACCOUNT)) $criteria->add(PaypalCustomerTableMap::VERIFIED_ACCOUNT, $this->verified_account);
        if ($this->isColumnModified(PaypalCustomerTableMap::ACCOUNT_TYPE)) $criteria->add(PaypalCustomerTableMap::ACCOUNT_TYPE, $this->account_type);
        if ($this->isColumnModified(PaypalCustomerTableMap::AGE_RANGE)) $criteria->add(PaypalCustomerTableMap::AGE_RANGE, $this->age_range);
        if ($this->isColumnModified(PaypalCustomerTableMap::PAYER_ID)) $criteria->add(PaypalCustomerTableMap::PAYER_ID, $this->payer_id);
        if ($this->isColumnModified(PaypalCustomerTableMap::POSTAL_CODE)) $criteria->add(PaypalCustomerTableMap::POSTAL_CODE, $this->postal_code);
        if ($this->isColumnModified(PaypalCustomerTableMap::LOCALITY)) $criteria->add(PaypalCustomerTableMap::LOCALITY, $this->locality);
        if ($this->isColumnModified(PaypalCustomerTableMap::REGION)) $criteria->add(PaypalCustomerTableMap::REGION, $this->region);
        if ($this->isColumnModified(PaypalCustomerTableMap::COUNTRY)) $criteria->add(PaypalCustomerTableMap::COUNTRY, $this->country);
        if ($this->isColumnModified(PaypalCustomerTableMap::STREET_ADDRESS)) $criteria->add(PaypalCustomerTableMap::STREET_ADDRESS, $this->street_address);
        if ($this->isColumnModified(PaypalCustomerTableMap::CREATED_AT)) $criteria->add(PaypalCustomerTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(PaypalCustomerTableMap::UPDATED_AT)) $criteria->add(PaypalCustomerTableMap::UPDATED_AT, $this->updated_at);

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
        $criteria = new Criteria(PaypalCustomerTableMap::DATABASE_NAME);
        $criteria->add(PaypalCustomerTableMap::ID, $this->id);
        $criteria->add(PaypalCustomerTableMap::PAYPAL_USER_ID, $this->paypal_user_id);

        return $criteria;
    }

    /**
     * Returns the composite primary key for this object.
     * The array elements will be in same order as specified in XML.
     * @return array
     */
    public function getPrimaryKey()
    {
        $pks = array();
        $pks[0] = $this->getId();
        $pks[1] = $this->getPaypalUserId();

        return $pks;
    }

    /**
     * Set the [composite] primary key.
     *
     * @param      array $keys The elements of the composite key (order must match the order in XML file).
     * @return void
     */
    public function setPrimaryKey($keys)
    {
        $this->setId($keys[0]);
        $this->setPaypalUserId($keys[1]);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return (null === $this->getId()) && (null === $this->getPaypalUserId());
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \PayPal\Model\PaypalCustomer (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setId($this->getId());
        $copyObj->setPaypalUserId($this->getPaypalUserId());
        $copyObj->setCreditCardId($this->getCreditCardId());
        $copyObj->setName($this->getName());
        $copyObj->setGivenName($this->getGivenName());
        $copyObj->setFamilyName($this->getFamilyName());
        $copyObj->setMiddleName($this->getMiddleName());
        $copyObj->setPicture($this->getPicture());
        $copyObj->setEmailVerified($this->getEmailVerified());
        $copyObj->setGender($this->getGender());
        $copyObj->setBirthday($this->getBirthday());
        $copyObj->setZoneinfo($this->getZoneinfo());
        $copyObj->setLocale($this->getLocale());
        $copyObj->setLanguage($this->getLanguage());
        $copyObj->setVerified($this->getVerified());
        $copyObj->setPhoneNumber($this->getPhoneNumber());
        $copyObj->setVerifiedAccount($this->getVerifiedAccount());
        $copyObj->setAccountType($this->getAccountType());
        $copyObj->setAgeRange($this->getAgeRange());
        $copyObj->setPayerId($this->getPayerId());
        $copyObj->setPostalCode($this->getPostalCode());
        $copyObj->setLocality($this->getLocality());
        $copyObj->setRegion($this->getRegion());
        $copyObj->setCountry($this->getCountry());
        $copyObj->setStreetAddress($this->getStreetAddress());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());
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
     * @return                 \PayPal\Model\PaypalCustomer Clone of current object.
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
     * Declares an association between this object and a ChildCustomer object.
     *
     * @param                  ChildCustomer $v
     * @return                 \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCustomer(ChildCustomer $v = null)
    {
        if ($v === null) {
            $this->setId(NULL);
        } else {
            $this->setId($v->getId());
        }

        $this->aCustomer = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCustomer object, it will not be re-added.
        if ($v !== null) {
            $v->addPaypalCustomer($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCustomer object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildCustomer The associated ChildCustomer object.
     * @throws PropelException
     */
    public function getCustomer(ConnectionInterface $con = null)
    {
        if ($this->aCustomer === null && ($this->id !== null)) {
            $this->aCustomer = CustomerQuery::create()->findPk($this->id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCustomer->addPaypalCustomers($this);
             */
        }

        return $this->aCustomer;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->paypal_user_id = null;
        $this->credit_card_id = null;
        $this->name = null;
        $this->given_name = null;
        $this->family_name = null;
        $this->middle_name = null;
        $this->picture = null;
        $this->email_verified = null;
        $this->gender = null;
        $this->birthday = null;
        $this->zoneinfo = null;
        $this->locale = null;
        $this->language = null;
        $this->verified = null;
        $this->phone_number = null;
        $this->verified_account = null;
        $this->account_type = null;
        $this->age_range = null;
        $this->payer_id = null;
        $this->postal_code = null;
        $this->locality = null;
        $this->region = null;
        $this->country = null;
        $this->street_address = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
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
        } // if ($deep)

        $this->aCustomer = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(PaypalCustomerTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildPaypalCustomer The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[PaypalCustomerTableMap::UPDATED_AT] = true;

        return $this;
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
