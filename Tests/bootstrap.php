<?php

declare(strict_types=1);

/*
 * Run from the root of a Thelia checkout (thelia/thelia) the module is installed in, against a
 * disposable database (see the Readme):
 *   php bin/test-prepare
 *   vendor/bin/phpunit -c phpunit.xml.dist --bootstrap local/modules/PayPal/Tests/bootstrap.php local/modules/PayPal/Tests
 */

use Symfony\Component\Dotenv\Dotenv;

$projectRoot = getcwd();

// The bootstrap of Thelia wires what the integration tests read: the generated Propel models and
// the database variables of the test environment.
if (is_file($projectRoot.'/tests/bootstrap.php')) {
    require $projectRoot.'/tests/bootstrap.php';

    return;
}

$loader = require $projectRoot.'/vendor/autoload.php';

$propelDirectory = $projectRoot.'/var/propel/test';
if (is_dir($propelDirectory.'/model')) {
    $loader->addPsr4('', $propelDirectory.'/model');
    $loader->addPsr4('TheliaMain\\', $propelDirectory.'/database/TheliaMain');
}

(new Dotenv())->bootEnv($projectRoot.'/.env');
