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


/**
 * Class PayPalEvents
 * @package PayPal\Event
 */
class PayPalEvents
{
    const PAYPAL_ORDER_CREATE = 'action.paypal.order.create';
    const PAYPAL_ORDER_UPDATE = 'action.paypal.order.update';
    const PAYPAL_ORDER_DELETE = 'action.paypal.order.delete';
    const PAYPAL_RECURSIVE_PAYMENT_CREATE = 'action.paypal.recursive.payment.create';

    const PAYPAL_AGREEMENT_CREATE = 'action.paypal.agreement.create';
    const PAYPAL_AGREEMENT_UPDATE = 'action.paypal.agreement.update';
    const PAYPAL_AGREEMENT_DELETE = 'action.paypal.agreement.delete';

    const PAYPAL_PLAN_CREATE = 'action.paypal.plan.create';
    const PAYPAL_PLAN_UPDATE = 'action.paypal.plan.update';
    const PAYPAL_PLAN_DELETE = 'action.paypal.plan.delete';

    const PAYPAL_CUSTOMER_CREATE = 'action.paypal.customer.create';
    const PAYPAL_CUSTOMER_UPDATE = 'action.paypal.customer.update';
    const PAYPAL_CUSTOMER_DELETE = 'action.paypal.customer.delete';

    const PAYPAL_CART_CREATE = 'action.paypal.cart.create';
    const PAYPAL_CART_UPDATE = 'action.paypal.cart.update';
    const PAYPAL_CART_DELETE = 'action.paypal.cart.delete';

    const PAYPAL_PLANIFIED_PAYMENT_CREATE = 'action.paypal.planified.payment.create';
    const PAYPAL_PLANIFIED_PAYMENT_UPDATE = 'action.paypal.planified.payment.update';
    const PAYPAL_PLANIFIED_PAYMENT_DELETE = 'action.paypal.planified.payment.delete';
}
