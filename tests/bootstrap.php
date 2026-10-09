<?php

declare(strict_types=1);

/*
 * The tests run from the shop the module is installed in: its autoloader knows the module and Thelia.
 */
$directory = __DIR__;

while (!is_file($directory.'/vendor/autoload.php')) {
    $parent = \dirname($directory);

    if ($parent === $directory) {
        throw new RuntimeException('Run the tests from a shop the module is installed in: no vendor/autoload.php above '.__DIR__);
    }

    $directory = $parent;
}

$loader = require $directory.'/vendor/autoload.php';
$loader->addPsr4('PayPal\\', \dirname(__DIR__).'/');
