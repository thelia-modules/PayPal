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

namespace PayPal\Service;

use Datetime;
use Monolog\Logger;
use MySQLHandler\MySQLHandler;
use PayPal\Model\Map\PaypalLogTableMap;
use PayPal\Model\PaypalLogQuery;
use PayPal\PayPal;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Propel;
use Thelia\Install\Database;

/**
 * Class PayPalLoggerService
 * @package PayPal\Service
 */
class PayPalLoggerService
{
    /**
     * @param $message
     * @param array $params
     * @param int $level
     * @throws PropelException
     */
    public static function log($message, array $params = [], int $level = Logger::DEBUG)
    {
        $staticParams = self::getStaticParams();

        $logger = new Logger(PayPal::getModuleCode());

        //Create MysqlHandler
        $mySQLHandler = new MyOwnSQLHandler(
            Propel::getConnection()->getWrappedConnection(),
            PaypalLogTableMap::TABLE_NAME,
            array_keys($staticParams),
            $level
        );

        $logger->pushHandler($mySQLHandler);

        //Now you can use the logger, and further attach additional information
        switch ($level) {
            case Logger::INFO:
                $logger->addRecord(LOG_INFO,$message, array_merge($staticParams, $params));
                break;

            case Logger::NOTICE:
                $logger->addRecord(LOG_NOTICE,$message, array_merge($staticParams, $params));
                break;

            case Logger::WARNING:
                $logger->addRecord(LOG_WARNING,$message, array_merge($staticParams, $params));
                break;

            case Logger::ERROR:
                $logger->addRecord(LOG_ERR,$message, array_merge($staticParams, $params));
                break;

            case Logger::CRITICAL:
                $logger->addRecord(LOG_CRIT,$message, array_merge($staticParams, $params));
                break;

            case Logger::ALERT:
                $logger->addRecord(LOG_ALERT,$message, array_merge($staticParams, $params));
                break;

            case Logger::EMERGENCY:
                $logger->addRecord(LOG_EMERG,$message, array_merge($staticParams, $params));
                break;

            default:
                $logger->addRecord(LOG_DEBUG,$message, array_merge($staticParams, $params));
                break;
        }

    }

    /**
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getStaticParams()
    {
        $psr3Fields = ['channel', 'level', 'message', 'time'];
        $payPalLogFields = PaypalLogTableMap::getFieldNames(PaypalLogTableMap::TYPE_FIELDNAME);
        $readableDate = new Datetime();

        $staticParams = [];
        foreach ($payPalLogFields as $fieldName) {

            // Do not interpret psr3 fields
            if (in_array($fieldName, $psr3Fields)) {
                continue;
            }

            if (in_array($fieldName, ['created_at', 'updated_at'])) {
                $staticParams[$fieldName] = $readableDate->format('Y-m-d H:i:s');
            } else {
                $staticParams[$fieldName] = null;
            }
        }

        return $staticParams;
    }
}
