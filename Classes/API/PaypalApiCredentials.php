<?php
/**
 * Class PaypalApiCredentials
 * Store PayPal API Credentials
 *
 * Created by JetBrains PhpStorm.
 * Date: 8/5/13
 * Time: 5:37 PM
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */

namespace Paypal\Classes\API;

use Paypal\Model\ConfigInterface;
use Paypal\Paypal;
use Thelia\Core\Translation\Translator;

class PaypalApiCredentials
{

    /** @var string PayPal API username  */
    protected $apiUsername = null;

    /** @var string PayPal API password */
    protected $apiPassword = null;

    /** @var string PayPal API signature (Three Token Authentication) */
    protected $apiSignature = null;

    /*
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Create a NVP Credentials
     *
     * @param ConfigInterface          $config Variable
     * @param string                   $user               PayPal API username
     * @param string                   $password           PayPal API password
     * @param string                   $signature          PayPal API signature (3T)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ConfigInterface $config, $user = null, $password = null, $signature = null)
    {
        $this->config=$config;
        if ($user === null && $password === null && $signature === null) {
            $this->setDefaultCredentials($config);
        } else {
            if (empty($user) || empty($password) || empty($signature)) {
                throw new \InvalidArgumentException(
                    'PaypalApiCredentials : Missing Argument'
                );
            }
            $this->apiPassword = $password;
            $this->apiSignature = $signature;
            $this->apiUsername = $user;

        }
    }

    /**
     * @return \Paypal\Model\ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set credentials from database according to SandBox Mode
     *
     * @param ConfigInterface $config Variable
     *
     * @throws \InvalidArgumentException
     */
    protected function setDefaultCredentials(ConfigInterface $config)
    {
        $paypalApiManager = new PaypalApiManager($config);
        if ($paypalApiManager->isModeSandbox()) {
            $username  = $config->getLoginSandbox() != null?$config->getLoginSandbox():"";
            $password  = $config->getPasswordSandbox() != null? $config->getPasswordSandbox():"";
            $signature = $config->getSignatureSandbox() != null? $config->getSignatureSandbox():"";
        } else {
            $username  = $config->getLogin() != null?$config->getLogin():"";
            $password  = $config->getPassword() != null?$config->getPassword():"";
            $signature = $config->getSignature() != null?$config->getSignature():"";
        }

        if (empty($username)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The username option must be set.'));
        }
        if (empty($password)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The password option must be set.'));
        }
        if (empty($signature)) {
            throw new \InvalidArgumentException(Translator::getInstance()->trans('The signature option must be set.'));
        }

        $this->apiUsername = $username;
        $this->apiPassword = $password;
        $this->apiSignature = $signature;
    }

    /**
     * Return API password
     *
     * @return string
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * Return API signature
     *
     * @return string
     */
    public function getApiSignature()
    {
        return $this->apiSignature;
    }

    /**
     * Return API username
     *
     * @return string
     */
    public function getApiUsername()
    {
        return $this->apiUsername;
    }

}
