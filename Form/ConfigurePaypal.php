<?php

namespace Paypal\Form;


use Paypal\Paypal;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class ConfigurePaypal extends BaseForm {
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
        //if($this->getName() == "")
        $path=__DIR__."/../".Paypal::JSON_CONFIG_PATH;
        if(is_readable($path)) {
            $json_data = json_decode(file_get_contents($path),true);
        }
        $this->formBuilder
            ->add("login","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("login"),
                'label_attr'=>array(
                    'for'=>'login'
                ),
                'data'=>(isset($json_data['login'])?$json_data['login']:""),
            ))
            ->add("password","text", array(
                    'constraints'=>array(new NotBlank()),
                    'label'=>Translator::getInstance()->trans("password"),
                    'label_attr'=>array(
                        'for'=>'password'
                    ),
                    'data'=>(isset($json_data['password'])?$json_data['password']:""),
                ))
            ->add("signature","text", array(
                'constraints'=>array(new NotBlank()),
                'label'=>Translator::getInstance()->trans("signature"),
                'label_attr'=>array(
                    'for'=>'signature'
                ),
                'data'=>(isset($json_data['signature'])?$json_data['signature']:""),
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

