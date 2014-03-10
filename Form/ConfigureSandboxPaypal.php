<?php

namespace Paypal\Form;

use Paypal\Model\PaypalConfig;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class ConfigureSandboxPaypal extends BaseForm
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
        $config_data = PaypalConfig::read();
        $this->formBuilder
            ->add("login","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("login"),
                'label_attr'=>array(
                    'for'=>'login'
                ),
                'data'=>(isset($config_data['login_sandbox'])?$config_data['login_sandbox']:""),
            ))
            ->add("password","text", array(
                    'constraints'=>array(new NotBlank()),
                    'label'=>Translator::getInstance()->trans("password"),
                    'label_attr'=>array(
                        'for'=>'password'
                    ),
                    'data'=>(isset($config_data['password_sandbox'])?$config_data['password_sandbox']:""),
                ))
            ->add("signature","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("signature"),
                'label_attr'=>array(
                    'for'=>'signature'
                ),
                'data'=>(isset($config_data['signature_sandbox'])?$config_data['signature_sandbox']:""),
            ))
            ->add("sandbox", "checkbox", array(
                'label'=>Translator::getInstance()->trans("Activate sandbox mode"),
                'label_attr'=>array(
                    'for'=>'sandbox'
                ),
                'value'=>(isset($config_data['sandbox'])?$config_data['sandbox']:"")
            ))
        ;
    }

    /**
     * @return string the name of your form. This name must be unique
     */
    public function getName()
    {
        return "configuresanboxpaypalform";
    }

}
