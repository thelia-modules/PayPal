<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Quentin Dufour
 * Date: 08/08/13
 * Time: 11:22
 */

namespace Paypal\Classes\API;

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

    protected $path;

    /**
     * Construct a new ApiLogManager
     *
     * @param string $name Name of the file where the log will be stored
     */
    public function __construct($name = 'general')
    {
        $dir = __DIR__ . "/../../logs";
        $this->path = $dir . '/'. $name . '.log';
        if(!is_dir($dir)) {
            if(is_writable(__DIR__ . "/../../")) {
                mkdir($dir);
            } else {
                throw new \Exception("Cannot create directory Paypal/logs. Please create it and make it writable or make directory Paypal writable.");
            }
        }
    }

    /**
     * Parse and log the return of the Paypal NVP API
     *
     * @param string $transaction A special string returned by the NVP API
     */
    public function logTransaction($transaction)
    {
        $logLine = '';
        $parsedTransaction = PaypalApiManager::nvpToArray($transaction);
        $date = new \DateTime($parsedTransaction['TIMESTAMP']);

        $logLine .= '[' . $this->getTransactionLogLevel($parsedTransaction) . '] ';
        $logLine .= $date->format('Y-m-d H:i:s') . ' ';
        $logLine .= 'Transaction ' . $parsedTransaction['ACK'] . ' ';
        $logLine .= 'correlationId: ' . $parsedTransaction['CORRELATIONID'] . ' ';

        if ($parsedTransaction !== null && array_key_exists('L_ERRORCODE0', $parsedTransaction)) {
            $logLine .= 'error: ';
            $logLine .= '[' . $parsedTransaction['L_ERRORCODE0'] . '] ';
            $logLine .= '<' . $parsedTransaction['L_SHORTMESSAGE0'] . '> ';
            $logLine .= $parsedTransaction['L_LONGMESSAGE0'] . ' ';
        }

        $this->write($logLine);
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
        $now = date('Y-m-d h:i:s');
        $log = "[$now] $category.$severity: $message.";
        $this->write($log);
    }

    /**
     * Return the transaction log level according to its acknowledgement
     *
     * @param array $parsedTransaction an array with nvp information
     *
     * @return string
     */
    protected function getTransactionLogLevel($parsedTransaction)
    {
        if ($parsedTransaction['ACK'] == PaypalApiErrorManager::ACK_SUCCESS) {
            return self::INFO;
        } else {
            return self::WARNING;
        }
    }

    /**
     * Write a previously formatted line in the log file.
     *
     * @param string $logLine The string to write in the file
     */
    protected function write($logLine)
    {
        $file = fopen($this->path, 'a');
        fwrite($file, $logLine . "\n");
        fclose($file);
    }
}