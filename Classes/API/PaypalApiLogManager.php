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

namespace Paypal\Classes\API;

use Thelia\Log\Tlog;

/**
 * Class PaypalApiLogManager
 * This class is the Paypal logger
 * Logged actions: transaction
 */
class PaypalApiLogManager
{
    /** @var Tlog $log */
    protected static $logger;

    /**
     * Parse and log the return of the Paypal NVP API
     *
     * @param string $transaction A special string returned by the NVP API
     */
    public function logTransaction($parsedTransaction)
    {
        if ($parsedTransaction) {
            /*
             * Then write
             */
            $logLine           = '';
            $date              = new \DateTime($parsedTransaction['TIMESTAMP']);

            $logLine .= $date->format('Y-m-d H:i:s') . ' ';
            $logLine .= 'Transaction ' . $parsedTransaction['ACK'] . ' ';
            $logLine .= 'correlationId: ' . $parsedTransaction['CORRELATIONID'] . ' ';

            if ($parsedTransaction !== null && array_key_exists('L_ERRORCODE0', $parsedTransaction)) {
                $logLine .= 'error: ';
                $logLine .= '[' . $parsedTransaction['L_ERRORCODE0'] . '] ';
                $logLine .= '<' . $parsedTransaction['L_SHORTMESSAGE0'] . '> ';
                $logLine .= $parsedTransaction['L_LONGMESSAGE0'] . ' ';
            }

            $this->getLogger()->info($logLine);
        } else {
            $this->getLogger()->info('No transaction was created.');
        }
    }

    public static function getLogFilePath()
    {
        return THELIA_LOG_DIR . DS . "log-paypal.txt";
    }

    /**
     * @return Tlog
     */
    public function getLogger()
    {
        if (self::$logger == null) {
            self::$logger = Tlog::getNewInstance();

            $logFilePath = self::getLogFilePath();

            self::$logger->setPrefix("#LEVEL: #DATE #HOUR: ");
            self::$logger->setDestinations("\\Thelia\\Log\\Destination\\TlogDestinationRotatingFile");
            self::$logger->setConfig("\\Thelia\\Log\\Destination\\TlogDestinationRotatingFile", 0, $logFilePath);
            self::$logger->setLevel(Tlog::INFO);
        }

        return self::$logger;
    }
}
