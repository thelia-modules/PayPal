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
namespace Paypal\Form;

use Paypal\Paypal;
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
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'login',
                'text',
                [
                    'constraints' =>  [ new NotBlank() ],
                    'label' => $this->translator->trans('login', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' =>  $this->translator->trans('Your Paypal login', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'constraints' =>  [ new NotBlank() ],
                    'label' => $this->translator->trans('password', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your Paypal password', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'signature',
                'text',
                [
                    'constraints' =>  [ new NotBlank() ],
                    'label' => $this->translator->trans('signature', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans('The Paypal signature', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'sandbox',
                'checkbox',
                [
                    'value' => 1,
                    'required' => false,
                    'label' => $this->translator->trans('Activate sandbox mode', [], Paypal::DOMAIN),
                ]
            )
            ->add(
                'sandbox_login',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('login', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' =>  $this->translator->trans('Your Paypal sandbox login', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'sandbox_password',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('password', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your Paypal sandbox password', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'sandbox_signature',
                'text',
                [
                    'required' => false,
                    'label' => $this->translator->trans('signature', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans('The Paypal sandbox signature', [], Paypal::DOMAIN)
                    ]
                ]
            )
            ->add(
                'allowed_ip_list',
                'textarea',
                [
                    'required' => false,
                    'label' => $this->translator->trans('Allowed IPs in test mode', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'List of IP addresses allowed to use this payment on the front-office when in test mode (your current IP is %ip). One address per line',
                            [ '%ip' => $this->getRequest()->getClientIp() ],
                            Paypal::DOMAIN
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
                    'label' => $this->translator->trans('Minimum order total', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Minimum order total in the default currency for which this payment method is available. Enter 0 for no minimum',
                            [],
                            Paypal::DOMAIN
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
                    'label' => $this->translator->trans('Maximum order total', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Maximum order total in the default currency for which this payment method is available. Enter 0 for no maximum',
                            [],
                            Paypal::DOMAIN
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
                    'label' => $this->translator->trans('Maximum items in cart', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'Maximum number of items in the customer cart for which this payment method is available.',
                            [],
                            Paypal::DOMAIN
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
                    'label' => $this->translator->trans('Send order confirmation on payment success', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, the order confirmation message is sent to the customer only when the payment is successful. The order notification is always sent to the shop administrator',
                            [],
                            Paypal::DOMAIN
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
                    'label' => $this->translator->trans('Send a payment confirmation e-mail', [], Paypal::DOMAIN),
                    'label_attr' => [
                        'help' => $this->translator->trans(
                            'If checked, a payment confirmation e-mail is sent to the customer.',
                            [],
                            Paypal::DOMAIN
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
        return "configurepaypalform";
    }
}
