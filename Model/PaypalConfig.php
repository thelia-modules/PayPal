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
namespace Paypal\Model;

use Paypal\Model\Base\PaypalConfig as BasePaypalConfig;
use Paypal\Paypal;

/**
 * Class PaypalConfig
 * @package Paypal\Model
 * @author Thelia <info@thelia.net>
 */
class PaypalConfig extends BasePaypalConfig implements ConfigInterface
{
    protected $login=null;
    protected $password=null;
    protected $signature=null;
    protected $sandbox=null;
    protected $login_sandbox=null;
    protected $password_sandbox=null;
    protected $signature_sandbox=null;

    /**
     * @return array|mixed ObjectCollection
     */
    protected function getDbValues($keysflag=true)
    {
        $pks = $this->getThisVars();
        if ($keysflag) {
            $pks=array_keys($pks);
        }
        $query = PaypalConfigQuery::create()
            ->findPks($pks);

        return $query;
    }

    /**
     * @param  null  $file
     * @return array
     */
    public static function read()
    {
        $pks = self::getSelfVars();

        return $pks;
    }

    /**
     * @return array
     */
    public static function getSelfVars()
    {
        $obj = new PaypalConfig();
        $obj->pushValues();
        $this_class_vars = get_object_vars($obj);
        $base_class_vars = get_class_vars("\\Paypal\\Model\\Base\\PaypalConfig");
        $pks = array_diff_key($this_class_vars, $base_class_vars);

        return $pks;
    }

    /**
     * @param null $file
     */
    public function write()
    {
        $dbvals = $this->getDbValues();
        $isnew=array();
        foreach ($dbvals as $var) {
            /** @var PaypalConfig $var */
            $isnew[$var->getName()] = true;
        }
        $this->pushValues();
        $vars=$this->getThisVars();
        foreach ($vars as $key=>$value) {
            $tmp = new PaypalConfig();
            $tmp->setNew(!isset($isnew[$key]));
            $tmp->setName($key);
            $tmp->setValue($value);
            $tmp->save();
        }
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
     * @param  string                        $login
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param  string                        $login_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLoginSandbox($login_sandbox)
    {
        $this->login_sandbox = $login_sandbox;

        return $this;
    }

    /**
     * @param  string                        $password
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param  string                        $password_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPasswordSandbox($password_sandbox)
    {
        $this->password_sandbox = $password_sandbox;

        return $this;
    }

    /**
     * @param  string                        $sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    /**
     * @param  string                        $signature
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @param  string                        $signature_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignatureSandbox($signature_sandbox)
    {
        $this->signature_sandbox = $signature_sandbox;

        return $this;
    }

    /**
     * @return array
     */
    protected function getThisVars()
    {
        $this_class_vars = get_object_vars($this);
        $base_class_vars = get_class_vars("\\Paypal\\Model\\Base\\PaypalConfig");
        $pks = array_diff_key($this_class_vars, $base_class_vars);

        return $pks;
    }

    public function pushValues()
    {
        $query = $this->getDbValues();
        foreach ($query as $var) {
            /** @var PaypalConfig $var */
            $name = $var->getName();
            if ($this->$name === null) {
                $this->$name = $var->getValue();
            }
        }
    }
}
