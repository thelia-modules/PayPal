<?php

namespace Paypal\Model;

use Thelia\Core\Translation\Translator;

class Config implements ConfigInterface {
    protected $login=null;
    protected $password=null;
    protected $signature=null;
    protected $sandbox="true"; // default value "true" to avoid some problems during tests...
    protected $login_sandbox=null;
    protected $password_sandbox=null;
    protected $signature_sandbox=null;

    /**
     * @param string $file
     * Read $file if not null and set values in instance
     */
    public function __construct($file=null)
    {
        $config=null;
        if($file !== null) {
            try {
                $config=$this->read($file);
            } catch(\Exception $e) {}
            if($config !== null) {
                foreach($config as $key=>$val) {
                    try {
                        $this->$key=$val;
                    } catch(\Exception $e) {}
                }
            }
        }
    }

    /**
     * @param string $file
     * @throws \Exception
     * write file with variables stored in instance of the class
     */
    public function write($file=null) {
        $path = __DIR__."/../".$file;
        if((file_exists($path) ? is_writable($path):is_writable(__DIR__."/../Config/"))) {
            $vars= get_object_vars($this);
            $file = fopen($path, 'w');
            fwrite($file, json_encode($vars));
            fclose($file);
        } else {
            throw new \Exception(Translator::getInstance()->trans("Can't write file ").$file.". ".
                Translator::getInstance()->trans("Please change the rights on the file and/or directory."));

        }
    }
    /**
     * @return array
     * Read $file and return a key value array
     */
    public static function read($file=null) {
        $path = __DIR__."/../".$file;
        $ret = null;
        if(is_readable($path)) {
            $json = json_decode(file_get_contents($path), true);
            if($json !== null) {
                $ret = $json;
            } else {
                throw new \Exception(Translator::getInstance()->trans("Can't read file ").$file.". ".
                    Translator::getInstance()->trans("The file is corrupted."));
            }
        } elseif(!file_exists($path)) {
            throw new \Exception(Translator::getInstance()->trans("The file ").$file.
                                Translator::getInstance()->trans(" doesn't exist. You have to create it in order to use this module. Please see module's configuration page."));
        } else {
            throw new \Exception(Translator::getInstance()->trans("Can't read file ").$file.". ".
                                Translator::getInstance()->trans("Please change the rights on the file."));

        }
        return $ret;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getLoginSandbox()
    {
        return $this->login_sandbox;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPasswordSandbox()
    {
        return $this->password_sandbox;
    }

    /**
     * @return bool
     */
    public function getSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function getSignatureSandbox()
    {
        return $this->signature_sandbox;
    }

    /**
     * @param string $login
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLogin($login)
    {
        $this->login = $login;
		return $this;
    }

    /**
     * @param string $login_sandbox
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLoginSandbox($login_sandbox)
    {
        $this->login_sandbox = $login_sandbox;
		return $this;
    }

    /**
     * @param string $password
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
		return $this;
    }

    /**
     * @param string $password_sandbox
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPasswordSandbox($password_sandbox)
    {
        $this->password_sandbox = $password_sandbox;
		return $this;
    }

    /**
     * @param string $sandbox
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;
		return $this;
    }

    /**
     * @param string $signature
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
		return $this;
    }

    /**
     * @param string $signature_sandbox
	 * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignatureSandbox($signature_sandbox)
    {
        $this->signature_sandbox = $signature_sandbox;
		return $this;
    }



}

