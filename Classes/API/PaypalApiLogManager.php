<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Quentin Dufour
 * Date: 08/08/13
 * Time: 11:22
 */

namespace Paypal\Classes\API;
use Thelia\Log\Tlog;
use Thelia\Log\TlogDestinationConfig;

/**
 * Class PaypalApiLogManager
 * This class is the Paypal logger
 * Logged actions: transaction
 */
class PaypalApiLogManager
{
    const EMERGENCY = 'EMERGENCY';
    const ALERT     = 'ALERT';
    const CRITICAL  = 'CRITICAL';
    const ERROR     = 'ERROR';
    const WARNING   = 'WARNING';
    const NOTICE    = 'NOTICE';
    const INFO      = 'INFO';
    const DEBUG     = 'DEBUG';

    const LOGCLASS = "\\Thelia\\Log\\Destination\\TlogDestinationFile";

    /** @var Tlog $log */
    protected $log;

    /**
     * Parse and log the return of the Paypal NVP API
     *
     * @param string $transaction A special string returned by the NVP API
     */
    public function logTransaction($transaction)
    {
        $this->setTLogPaypal();
        /*
         * Then write
         */
        $logLine = '';
        $parsedTransaction = PaypalApiManager::nvpToArray($transaction);
        $date = new \DateTime($parsedTransaction['TIMESTAMP']);

        $logLine .= $date->format('Y-m-d H:i:s') . ' ';
        $logLine .= 'Transaction ' . $parsedTransaction['ACK'] . ' ';
        $logLine .= 'correlationId: ' . $parsedTransaction['CORRELATIONID'] . ' ';

        if ($parsedTransaction !== null && array_key_exists('L_ERRORCODE0', $parsedTransaction)) {
            $logLine .= 'error: ';
            $logLine .= '[' . $parsedTransaction['L_ERRORCODE0'] . '] ';
            $logLine .= '<' . $parsedTransaction['L_SHORTMESSAGE0'] . '> ';
            $logLine .= $parsedTransaction['L_LONGMESSAGE0'] . ' ';
        }


        $this->log->info($logLine);
        $this->getBackToPreviousState();
    }

    /**
     * Log a message
     *
     * @param string $message  Message
     * @param string $severity EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG
     * @param string $category Category
     */

    public function logText($message, $severity = 'INFO', $category = 'paypal')
    {
        $this->setTLogPaypal();


        $now = date('Y-m-d h:i:s');
        $msg = "[$now] $category.$severity: $message.";
        $this->log->info($msg);

        // Back to previous state
        $this->getBackToPreviousState();
    }

    /**
     * @return Tlog
     */
    protected function setTLogPaypal()
    {
        /*
         * Write Log
         */
        $this->log = Tlog::getInstance();
        $this->log->setDestinations(self::LOGCLASS);
        $this->log->setConfig(self::LOGCLASS, 0, THELIA_ROOT . "log" . DS . "log-paypal.txt");
    }

    protected function getBackToPreviousState()
    {
        $this->log->setDestinations("\\Thelia\\Log\\Destination\\TlogDestinationRotatingFile");
    }

}