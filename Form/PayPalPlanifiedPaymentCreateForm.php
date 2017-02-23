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

use PayPal\PayPal;
use PayPal\Service\PayPalAgreementService;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class PayPalPlanifiedPaymentCreateForm extends BaseForm
{
    const FORM_NAME = 'paypal_planified_payment_create_form';

    /**
     * @return null
     */
    protected function buildForm()
    {
        /** @var \Thelia\Model\Lang $lang */
        $lang = $this->getRequest()->getSession()->get('thelia.current.lang');

        $this->formBuilder
            ->add(
                PayPalFormFields::FIELD_PP_LOCALE,
                HiddenType::class,
                [
                    'constraints' => [
                        new NotBlank()
                    ],
                    'required' => true,
                    'data' => $lang->getLocale(),
                    'label' => $this->trans('The locale of the planified payment'),
                    'label_attr'  => ['for' => PayPalFormFields::FIELD_PP_LOCALE]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_TITLE,
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank()
                    ],
                    'required' => true,
                    'label' => $this->trans('The title of the planified payment'),
                    'label_attr'  => ['for' => PayPalFormFields::FIELD_PP_TITLE]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_DESCRIPTION,
                TextType::class,
                [
                    'required' => false,
                    'label' => $this->trans('The description of the planified payment'),
                    'label_attr'  => ['for' => PayPalFormFields::FIELD_PP_DESCRIPTION]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_FREQUENCY_INTERVAL,
                'integer',
                [
                    'label' => $this->trans('Frequency interval'),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PP_FREQUENCY_INTERVAL],
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThan(['value' => 0])
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_FREQUENCY,
                'choice',
                [
                    'choices' => PayPalAgreementService::getAllowedPaymentFrequency(),
                    'label' => $this->trans('Frequency'),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PP_FREQUENCY],
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_CYCLE,
                'integer',
                [
                    'label' => $this->trans('Cycle'),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PP_CYCLE],
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThan(['value' => 0])
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_MIN_AMOUNT,
                'number',
                [
                    'label' => $this->trans('Min amount'),
                    'label_attr' => [
                        'for' => PayPalFormFields::FIELD_PP_MIN_AMOUNT,
                        'help' => $this->trans("Let value to 0 if you don't want a minimum")
                    ],
                    'required' => false,
                    'constraints' => [
                        new GreaterThanOrEqual(['value' => 0])
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_MAX_AMOUNT,
                'number',
                [
                    'label' => $this->trans('Max amount'),
                    'label_attr' => [
                        'for' => PayPalFormFields::FIELD_PP_MAX_AMOUNT,
                        'help' => $this->trans("Let value to 0 if you don't want a maximum")
                    ],
                    'required' => false,
                    'constraints' => [
                        new GreaterThanOrEqual(['value' => 0])
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_PP_POSITION,
                'integer',
                [
                    'label' => $this->trans('Position'),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_PP_POSITION],
                    'required' => false
                ]
            )
        ;
    }

    /**
     * @return string the name of your form. This name must be unique
     */
    public function getName()
    {
        return self::FORM_NAME;
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function trans($id, array $parameters = [], $domain = null)
    {
        return Translator::getInstance()->trans(
            $id,
            $parameters,
            $domain === null ? PayPal::DOMAIN_NAME : $domain
        );
    }
}
