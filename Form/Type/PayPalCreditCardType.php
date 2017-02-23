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

namespace PayPal\Form\Type;

use PayPal\Form\PayPalFormFields;
use PayPal\PayPal;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Form\Type\AbstractTheliaType;
use Thelia\Core\Translation\Translator;


/**
 * Class PayPalCreditCardType
 * @package PayPal\Form\Type
 */
class PayPalCreditCardType extends AbstractTheliaType
{
    const TYPE_NAME = 'paypal_credit_card_type';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                PayPalFormFields::FIELD_CARD_TYPE,
                'choice',
                [
                    'choices' => $this->getTypes(),
                    'label' => Translator::getInstance()->trans('Card type', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_CARD_TYPE],
                    'required' => false,
                    'constraints' => [
                        new Callback(
                            [
                                'methods' => [
                                    [$this, 'verifyCardType']
                                ],
                            ]
                        )
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_CARD_NUMBER,
                'text',
                [
                    'label' => Translator::getInstance()->trans('Card number', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_CARD_NUMBER],
                    'required' => false,
                    'constraints' => [
                        new Callback(
                            [
                                'methods' => [
                                    [$this, 'verifyCardNumber']
                                ],
                            ]
                        )
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_CARD_EXPIRE_MONTH,
                'choice',
                [
                    'choices' => $this->getMonths(),
                    'label' => Translator::getInstance()->trans('Expire month', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_CARD_EXPIRE_MONTH],
                    'required' => false,
                    'constraints' => [
                        new Callback(
                            [
                                'methods' => [
                                    [$this, 'verifyCardExpireMonth']
                                ],
                            ]
                        )
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_CARD_EXPIRE_YEAR,
                'choice',
                [
                    'choices' => $this->getYears(),
                    'label' => Translator::getInstance()->trans('Expire year', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_CARD_EXPIRE_YEAR],
                    'required' => false,
                    'constraints' => [
                        new Callback(
                            [
                                'methods' => [
                                    [$this, 'verifyCardExpireYear']
                                ],
                            ]
                        )
                    ]
                ]
            )
            ->add(
                PayPalFormFields::FIELD_CARD_CVV,
                'text',
                [
                    'label' => Translator::getInstance()->trans('CVV', [], PayPal::DOMAIN_NAME),
                    'label_attr' => ['for' => PayPalFormFields::FIELD_CARD_CVV],
                    'required' => false,
                    'constraints' => [
                        new Callback(
                            [
                                'methods' => [
                                    [$this, 'verifyCardCVV']
                                ],
                            ]
                        )
                    ]
                ]
            )
        ;
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function verifyCardType($value, ExecutionContextInterface $context)
    {
        $this->checkNotBlank($value, $context);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function verifyCardNumber($value, ExecutionContextInterface $context)
    {
        $this->checkNotBlank($value, $context);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function verifyCardExpireMonth($value, ExecutionContextInterface $context)
    {
        $this->checkNotBlank($value, $context);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function verifyCardExpireYear($value, ExecutionContextInterface $context)
    {
        $this->checkNotBlank($value, $context);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function verifyCardCVV($value, ExecutionContextInterface $context)
    {
        $this->checkNotBlank($value, $context);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    protected function checkNotBlank($value, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();
        if (isset($data[PayPalFormFields::FIELD_PAYMENT_MODULE]) && PayPal::getModuleId() === $data[PayPalFormFields::FIELD_PAYMENT_MODULE]) {
            if (isset($data[PayPalFormFields::FIELD_PAYPAL_METHOD]) && PayPal::PAYPAL_METHOD_CREDIT_CARD === $data[PayPalFormFields::FIELD_PAYPAL_METHOD]) {
                if (false === $value || (empty($value) && '0' != $value)) {
                    $context->addViolation(
                        Translator::getInstance()->trans('This value should not be blank', [], PayPal::DOMAIN_NAME)
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    /**
     * @return array
     */
    protected function getTypes()
    {
        return [
            PayPal::CREDIT_CARD_TYPE_VISA => 'Visa',
            PayPal::CREDIT_CARD_TYPE_MASTERCARD => 'MasterCard',
            PayPal::CREDIT_CARD_TYPE_DISCOVER => 'Discover',
            PayPal::CREDIT_CARD_TYPE_AMEX => 'Amex'
        ];
    }

    /**
     * @return array
     */
    protected function getMonths()
    {
        return [
            1 => '01',
            2 => '02',
            3 => '03',
            4 => '04',
            5 => '05',
            6 => '06',
            7 => '07',
            8 => '08',
            9 => '09',
            10 => '10',
            11 => '11',
            12 => '12'
        ];
    }

    /**
     * @return array
     */
    protected function getYears()
    {
        $actualYear = date("Y");

        $years = [];
        $years[(int)$actualYear] = $actualYear;
        for ($i = 1; $i <= 10; $i++) {
            $years[(int)($actualYear + $i)] = $actualYear + $i;
        }
        return $years;
    }
}
