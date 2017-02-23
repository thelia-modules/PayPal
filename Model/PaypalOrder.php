<?php

namespace PayPal\Model;

use PayPal\Model\Base\PaypalOrder as BasePaypalOrder;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;

class PaypalOrder extends BasePaypalOrder
{
    /**
     * Get the associated ChildOrder object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return     Order The associated ChildOrder object.
     * @throws PropelException
     */
    public function getOrder(ConnectionInterface $con = null)
    {
        if ($this->aOrder === null && ($this->id !== null)) {
            $this->aOrder = OrderQuery::create()->findPk($this->id, $con);
        }

        return $this->aOrder;
    }

    /**
     * Declares an association between this object and a ChildOrder object.
     *
     * @param                  Order $order
     * @return                 \PayPal\Model\PaypalOrder The current object (for fluent API support)
     * @throws PropelException
     */
    public function setOrder(Order $order = null)
    {
        if ($order === null) {
            $this->setId(NULL);
        } else {
            $this->setId($order->getId());
        }

        $this->aOrder = $order;

        return $this;
    }
}
