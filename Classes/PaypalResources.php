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

namespace Paypal\Classes;

class PaypalResources
{
    const LOGO_NORMAL_URL = 'https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg';
    const LOGO_PAIEMENT_CARDS_URL = 'https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_moyens_paiement_fr.jpg';

    const PAYPAL_REDIRECT_NORMAL_URL = 'https://www.paypal.com/cgi-bin/webscr';
    const PAYPAL_REDIRECT_SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    const CMD_EXPRESS_CHECKOUT_KEY = '_express-checkout';
    const CMD_EXPRESS_CHECKOUT_MOBILE_KEY = '_express-checkout-mobile';
}
