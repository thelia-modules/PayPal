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
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * Class ConfigurePaypal
 * @package Paypal\Form
 * @author Thelia <info@thelia.net>
 */
class ConfigurationForm extends BaseForm
{
    const FORM_NAME = 'paypal_form_configure';

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'login',
                'text',
                [
                    'constraints' =>  [ new NotBlank() ],
                    'label' => $this->translator->trans('login', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' =>  $this->translator->trans('Your Paypal login', [], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'constraints' =>  [ new NotBlank() ],
                    'label' => $this->translator->trans('password', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your Paypal password', [], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'merchant_id',
                'text',
                [
                    'label' => $this->translator->trans('Merchant ID', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('The Paypal <a target="_blank" href="%url">identity merchant account</a>', ['%url' => 'https://www.paypal.com/businessprofile/settings/'], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'sandbox',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate sandbox mode', [], PayPal::DOMAIN_NAME),
                ]
            )
            ->add(
                'sandbox_login',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('login', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' =>  $this->translator->trans('Your Paypal sandbox login', [], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'sandbox_password',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('password', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your Paypal sandbox password', [], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'sandbox_merchant_id',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('Merchant ID', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('The Paypal <a target="_blank" href="%url">identity merchant account</a>', ['%url' => 'https://www.paypal.com/businessprofile/settings/'], PayPal::DOMAIN_NAME)
                    ]
                ]
            )
            ->add(
                'allowed_ip_list',
                'textarea',
                [
                    'required' => false,
                    'label' => $this->translator->trans('Allowed IPs in test mode', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'List of IP addresses allowed to use this payment on the front-office when in test mode (your current IP is %ip). One address per line',
                            [ '%ip' => $this->getRequest()->getClientIp() ],
                            PayPal::DOMAIN_NAME
                        )
                    ],
                    'attr' => [
                        'rows' => 3
                    ]
                ]
            )
            ->add(
                'minimum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(array('value' => 0))
                    ],
                    'required' => false,
                    'label' => $this->translator->trans('Minimum order total', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Minimum order total in the default currency for which this payment method is available. Enter 0 for no minimum',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'maximum_amount',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(array('value' => 0))
                    ],
                    'required' => false,
                    'label' => $this->translator->trans('Maximum order total', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Maximum order total in the default currency for which this payment method is available. Enter 0 for no maximum',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'cart_item_count',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(array('value' => 0))
                    ],
                    'required' => false,
                    'label' => $this->translator->trans('Maximum items in cart', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Maximum number of items in the customer cart for which this payment method is available.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'method_paypal',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate payment with PayPal account', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order can be paid by PayPal account.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'method_paypal_with_in_context',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Use InContext mode for classic PayPal payment', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, a PayPal popup will be used to execute the payment.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'method_express_checkout',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate Express Checkout payment with PayPal', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order can be paid directly from cart.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'method_credit_card',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate payment with credit card', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order can be paid by credit card.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'method_planified_payment',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate payment with planified payment', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order can be paid by planified payement.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'send_confirmation_message_only_if_paid',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Send order confirmation on payment success', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order confirmation message is sent to the customer only when the payment is successful. The order notification is always sent to the shop administrator',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'send_payment_confirmation_message',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Send a payment confirmation e-mail', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, a payment confirmation e-mail is sent to the customer.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
                ]
            )
            ->add(
                'send_recursive_message',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Send a recursive payment confirmation e-mail', [], PayPal::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, a payment confirmation e-mail is sent to the customer after each PayPal transaction.',
                            [],
                            PayPal::DOMAIN_NAME
                        )
                    ]
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
}
