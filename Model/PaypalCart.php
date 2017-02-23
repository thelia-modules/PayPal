<?php

namespace PayPal\Model;

use PayPal\Model\Base\PaypalCart as BasePaypalCart;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Cart;
use Thelia\Model\CartQuery;

class PaypalCart extends BasePaypalCart
{
    /**
     * Get the associated ChildCart object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return     Cart The associated ChildCart object.
     * @throws PropelException
     */
    public function getCart(ConnectionInterface $con = null)
    {
        if ($this->aCart === null && ($this->id !== null)) {
            $this->aCart = CartQuery::create()->findPk($this->id, $con);
        }

        return $this->aCart;
    }

    /**
     * Declares an association between this object and a ChildCart object.
     *
     * @param  Cart $cart
     * @return \PayPal\Model\PaypalCart The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCart(Cart $cart = null)
    {
        if ($cart === null) {
            $this->setId(NULL);
        } else {
            $this->setId($cart->getId());
        }

        $this->aCart = $cart;

        return $this;
    }
}
