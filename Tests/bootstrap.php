<?php

declare(strict_types=1);

/*
 * Run from the root of the Thelia project the module is installed in, against a disposable
 * database whose name ends with `_test` (see the Readme):
 *   php bin/test-prepare
 *   vendor/bin/phpunit --no-configuration --bootstrap vendor/thelia/modules/PayPal/Tests/bootstrap.php vendor/thelia/modules/PayPal/Tests
 */

use Symfony\Component\Dotenv\Dotenv;

$projectRoot = getcwd();

require $projectRoot.'/bootstrap.php';
require $projectRoot.'/vendor/autoload.php';

(new Dotenv())->bootEnv($projectRoot.'/.env');
