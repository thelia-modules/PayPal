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

use PayPal\Model\PaypalPlan;
use Thelia\Core\Event\ActionEvent;

/**
 * Class PayPalPlanEvent
 * @package PayPal\Event
 */
class PayPalPlanEvent extends ActionEvent
{
    /** @var PaypalPlan */
    protected $payPalPlan;

    /**
     * PayPalPlanEvent constructor.
     * @param PaypalPlan $payPalPlan
     */
    public function __construct(PaypalPlan $payPalPlan)
    {
        $this->payPalPlan = $payPalPlan;
    }

    /**
     * @return PaypalPlan
     */
    public function getPayPalPlan()
    {
        return $this->payPalPlan;
    }

    /**
     * @param PaypalPlan $payPalPlan
     *
     * @return $this
     */
    public function setPayPalPlan($payPalPlan)
    {
        $this->payPalPlan = $payPalPlan;

        return $this;
    }
}
