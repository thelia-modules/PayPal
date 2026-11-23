<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PayPal\Tests;

use Symfony\Component\Process\Process;
use Thelia\Model\ModuleQuery;

/**
 * For the integration tests that need the module active: its routes, its tables and its models.
 */
trait ActivatedPayPal
{
    /**
     * PayPal ships inactive (enabled-by-default 0) and an inactive module declares no route: when the container of
     * the test kernel has no PayPal route, activate the module in the test database and rebuild that container,
     * whatever bootstrap runs the suite. The console runs with the database and debug mode of the test kernel, so
     * that neither the activation nor the rebuilt container reach another database set in the shell.
     */
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $container = static::getContainer();
        $routesLoaded = null !== $container->get('router')->getRouteCollection()->get('paypal_front_pay');
        $environment = [];
        foreach (['host', 'port', 'name', 'user', 'password'] as $setting) {
            $environment['DATABASE_'.strtoupper($setting)] = (string) $container->getParameter('thelia.database_'.$setting);
        }
        self::ensureKernelShutdown();

        if ($routesLoaded) {
            return;
        }

        $console = [\PHP_BINARY, $kernel->getProjectDir().'/bin/console'];
        $options = ['--env='.$kernel->getEnvironment(), ...($kernel->isDebug() ? [] : ['--no-debug'])];

        $activation = new Process([...$console, 'module:activate', 'PayPal', ...$options], $kernel->getProjectDir(), $environment);
        $activation->run();

        if (!$activation->isSuccessful() && !str_contains($activation->getOutput(), 'already activated')) {
            throw new \RuntimeException("Could not activate PayPal in the test database:\n".$activation->getOutput().$activation->getErrorOutput());
        }

        (new Process([...$console, 'cache:clear', ...$options], $kernel->getProjectDir(), $environment))->setTimeout(null)->mustRun();

        ModuleQuery::resetActivated();
    }
}
