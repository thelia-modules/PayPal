<?php
/*************************************************************************************/
/*      Copyright (c) OpenStudio                                                     */
/*      web : https://www.openstudio.fr                                              */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace PayPal\Service;

use Monolog\Logger;
use MySQLHandler\MySQLHandler;
use PDO;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Created by Franck Allimant, OpenStudio <fallimant@openstudio.fr>
 * Projet: parquets-et-lambris-de-vallereuil.com
 * Date: 16/03/2023
 */
class MyOwnSQLHandler extends MySQLHandler
{
    public function __construct(PDO|ConnectionInterface $pdo = null, string $table, array $additionalFields = [], bool|int $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->pdo = $pdo;

        parent::__construct(null, $table, $additionalFields, $level, $bubble);
    }
}
