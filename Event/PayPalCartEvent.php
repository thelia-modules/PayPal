<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace PayPal\Event;

use PayPal\Model\PaypalCart;
use Thelia\Core\Event\ActionEvent;

/**
 * Class PayPalCartEvent
 * @package PayPal\Event
 */
class PayPalCartEvent extends ActionEvent
{
    /** @var PaypalCart */
    protected $payPalCart;

    /**
     * PayPalCartEvent constructor.
     * @param PaypalCart $payPalCart
     */
    public function __construct(PaypalCart $payPalCart)
    {
        $this->payPalCart = $payPalCart;
    }

    /**
     * @return PaypalCart
     */
    public function getPayPalCart()
    {
        return $this->payPalCart;
    }

    /**
     * @param PaypalCart $payPalCart
     *
     * @return $this
     */
    public function setPayPalCart($payPalCart)
    {
        $this->payPalCart = $payPalCart;

        return $this;
    }
}
