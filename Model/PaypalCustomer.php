<?php

namespace PayPal\Model;

use PayPal\Api\OpenIdUserinfo;
use PayPal\Model\Base\PaypalCustomer as BasePaypalCustomer;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;

class PaypalCustomer extends BasePaypalCustomer
{
    /** @var OpenIdUserinfo */
    protected $openIdUserinfo;

    /**
     * Get the associated ChildCustomer object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return     Customer The associated ChildCustomer object.
     * @throws PropelException
     */
    public function getCustomer(ConnectionInterface $con = null)
    {
        if ($this->aCustomer === null && ($this->id !== null)) {
            $this->aCustomer = CustomerQuery::create()->findPk($this->id, $con);
        }

        return $this->aCustomer;
    }

    /**
     * Declares an association between this object and a ChildCustomer object.
     *
     * @param  Customer $customer
     * @return \PayPal\Model\PaypalCustomer The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCustomer(Customer $customer = null)
    {
        if ($customer === null) {
            $this->setId(NULL);
        } else {
            $this->setId($customer->getId());
        }

        $this->aCustomer = $customer;

        return $this;
    }
}
