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

// PayPal ships inactive (enabled-by-default 0) and an inactive module declares no route: activate it in the test
// database before the first test compiles the container.
exec(
    escapeshellarg(PHP_BINARY).' '.escapeshellarg($projectRoot.'/bin/console').' module:activate PayPal --env=test 2>&1',
    $activationOutput,
    $activationStatus,
);

if (0 !== $activationStatus && !str_contains(implode("\n", $activationOutput), 'already activated')) {
    throw new RuntimeException("Could not activate PayPal in the test database:\n".implode("\n", $activationOutput));
}
