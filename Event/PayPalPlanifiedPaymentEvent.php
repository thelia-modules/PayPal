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

use PayPal\Model\PaypalPlanifiedPayment;
use Thelia\Core\Event\ActionEvent;

/**
 * Class PayPalPlanifiedPaymentEvent
 * @package PayPal\Event
 */
class PayPalPlanifiedPaymentEvent extends ActionEvent
{
    /** @var PaypalPlanifiedPayment */
    protected $payPalPlanifiedPayment;

    /**
     * PayPalPlanifiedPaymentEvent constructor.
     * @param PaypalPlanifiedPayment $payPalPlanifiedPayment
     */
    public function __construct(PaypalPlanifiedPayment $payPalPlanifiedPayment)
    {
        $this->payPalPlanifiedPayment = $payPalPlanifiedPayment;
    }

    /**
     * @return PaypalPlanifiedPayment
     */
    public function getPayPalPlanifiedPayment()
    {
        return $this->payPalPlanifiedPayment;
    }

    /**
     * @param PaypalPlanifiedPayment $payPalPlanifiedPayment
     *
     * @return $this
     */
    public function setPayPalPlanifiedPayment($payPalPlanifiedPayment)
    {
        $this->payPalPlanifiedPayment = $payPalPlanifiedPayment;

        return $this;
    }
}
