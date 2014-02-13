<?php

namespace Paypal\Controller;

use Paypal\Model\PaypalConfig;
use Paypal\Paypal;
use Thelia\Controller\Admin\BaseAdminController;

/**
 * Class ConfigurePaypal
 * @package Paypal\Controller
 * @author Thelia <info@thelia.net>
 */
class ConfigurePaypal extends BaseAdminController {

    /*
     * Checks paypal.configure || paypal.configure.sandbox form and save config into json file
     */
    public function configure() {
        $conf = new PaypalConfig();
        $one_is_done=0;
        $tab="";
        // Case form is paypal.configure
        $form = new \Paypal\Form\ConfigurePaypal($this->getRequest());
        try {
            $vform = $this->validateForm($form);
            $conf->setLogin($vform->get('login')->getData())
                ->setPassword($vform->get('password')->getData())
                ->setSignature($vform->get('signature')->getData())
                ->write();
            $one_is_done=1;
            $tab="configure_account";
        } catch (\Exception $e) {}
        // Case form is paypal.configure.sandbox
        $form = new \Paypal\Form\ConfigureSandboxPaypal($this->getRequest());
        try {
            $vform = $this->validateForm($form);
            $one_is_done=2;
            $tab="configure_sandbox";
            $conf->setLoginSandbox($vform->get('login')->getData())
                ->setPasswordSandbox($vform->get('password')->getData())
                ->setSignatureSandbox($vform->get('signature')->getData())
                ->setSandbox($vform->get('sandbox')->getData() ?"true":"");

                $conf->write();

        } catch (\Exception $e) {}
        //Redirect to module configuration page
        $this->redirectToRoute("admin.module.configure",array(),
            array ( 'module_code'=>Paypal::getModCode(true),
                'current_tab'=>$tab,
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'));
    }
}