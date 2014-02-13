<?php
namespace Paypal\Model;

interface ConfigInterface {
    // Data access
    public function write();
    public static function read();

    // variables setters
    /**
     * @param string $login
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLogin($login);

    /**
     * @param string $login_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setLoginSandbox($login_sandbox);

    /**
     * @param string $password
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPassword($password);

    /**
     * @param string $password_sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setPasswordSandbox($password_sandbox);

    /**
     * @param string $sandbox
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSandbox($sandbox);

    /**
     * @param string $signature
     * @return \Paypal\Model\ConfigInterface $this
     */
    public function setSignature($signature);

    /**
     * @param string $signature_sandbox
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
