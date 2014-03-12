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

/**
 * Interface ConfigInterface
 * @package Paypal\Model
 * @author Thelia <info@thelia.net>
 */
interface ConfigInterface
{
    // Data access
    public function write();
    public static function read();

    // variables setters
    /**
     * @param  string                        $login
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLogin($login);

    /**
     * @param  string                        $login_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLoginSandbox($login_sandbox);

    /**
     * @param  string                        $password
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPassword($password);

    /**
     * @param  string                        $password_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPasswordSandbox($password_sandbox);

    /**
     * @param  string                        $sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSandbox($sandbox);

    /**
     * @param  string                        $signature
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignature($signature);

    /**
     * @param  string                        $signature_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignatureSandbox($signature_sandbox);

    /**
     * @return string
     */
    public function getLogin();

    /**
     * @return string
     */
    public function getLoginSandbox();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getPasswordSandbox();

    /**
     * @return bool
     */
    public function getSandbox();

    /**
     * @return string
     */
    public function getSignature();

    /**
     * @return string
     */
    public function getSignatureSandbox();
}
