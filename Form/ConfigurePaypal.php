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

use Paypal\Model\PaypalConfig;
use Paypal\Paypal;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

/**
 * Class ConfigurePaypal
 * @package Paypal\Form
 * @author Thelia <info@thelia.net>
 */
class ConfigurePaypal extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $config_data=PaypalConfig::read();
        $this->formBuilder
            ->add("login","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("login"),
                'label_attr'=>array(
                    'for'=>'login'
                ),
                'data'=>(isset($config_data['login'])?$config_data['login']:""),
            ))
            ->add("password","text", array(
                    'constraints'=>array(new NotBlank()),
                    'label'=>Translator::getInstance()->trans("password"),
                    'label_attr'=>array(
                        'for'=>'password'
                    ),
                    'data'=>(isset($config_data['password'])?$config_data['password']:""),
                ))
            ->add("signature","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("signature"),
                'label_attr'=>array(
                    'for'=>'signature'
                ),
                'data'=>(isset($config_data['signature'])?$config_data['signature']:""),
            ))

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
