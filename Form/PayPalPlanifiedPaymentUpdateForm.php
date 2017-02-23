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

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;

class PayPalPlanifiedPaymentUpdateForm extends PayPalPlanifiedPaymentCreateForm
{
    const FORM_NAME = 'paypal_planified_payment_update_form';

    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add(
                PayPalFormFields::FIELD_PP_ID,
                HiddenType::class,
                [
                    'required' => true,
                    'label_attr'  => ['for' => PayPalFormFields::FIELD_PP_ID],
                    'constraints'  => [
                        new NotBlank()
                    ]
                ]
            )
        ;
    }

    public function getName()
    {
        return self::FORM_NAME;
    }
}
