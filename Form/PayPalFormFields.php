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

namespace PayPal\Form;

/**
 * Class PayPalFormFields
 * @package PayPal\Form
 */
class PayPalFormFields
{
    const FIELD_PAYMENT_MODULE = 'payment-module';

    // \Thelia\Form\OrderPayment
    const FIELD_PAYPAL_METHOD = 'paypal_method';
    const FIELD_PAYPAL_PLANIFIED_PAYMENT = 'paypal_planified_payment';

    // \Form\Type\PayPalCreditCardType
    const FIELD_CARD_TYPE = 'card_type';
    const FIELD_CARD_NUMBER = 'card_number';
    const FIELD_CARD_EXPIRE_MONTH = 'card_expire_month';
    const FIELD_CARD_EXPIRE_YEAR = 'card_expire_year';
    const FIELD_CARD_CVV = 'card_cvv';

    // \Form\PayPalPlanifiedPaymentForm
    const FIELD_PP_ID = 'id';
    const FIELD_PP_LOCALE = 'locale';
    const FIELD_PP_TITLE = 'title';
    const FIELD_PP_DESCRIPTION = 'description';
    const FIELD_PP_FREQUENCY = 'frequency';
    const FIELD_PP_FREQUENCY_INTERVAL = 'frequency_interval';
    const FIELD_PP_CYCLE = 'cycle';
    const FIELD_PP_MIN_AMOUNT = 'min_amount';
    const FIELD_PP_MAX_AMOUNT = 'max_amount';
    const FIELD_PP_POSITION = 'position';
}
