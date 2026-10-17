<?php

namespace PayPal\EventListeners;

use PayPal\PayPal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Thelia\Model\ModuleConfigQuery;

class ConfigListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'module.config' => [
                'onModuleConfig', 128
            ],
        ];
    }

    public function onModuleConfig(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if ($subject !== "HealthStatus") {
            throw new \RuntimeException('Event subject does not match expected value');
        }

        $configModule = ModuleConfigQuery::create()
            ->filterByModuleId(PayPal::getModuleId())
            ->filterByName(['login', 'password', 'minimum_amount', 'maximum_amount', 'cart_item_count'])
            ->find();

        $moduleConfig = [];

        $moduleConfig['module'] = PayPal::getModuleCode();
        $configsCompleted = true;

        if ($configModule->count() === 0) {
            $configsCompleted = false;
        }

        foreach ($configModule as $config) {
            $moduleConfig[$config->getName()] = $config->getValue();
            if ($config->getValue() === null) {
                $configsCompleted = false;
            }
        }

        if (!isset($moduleConfig['login']) || !isset($moduleConfig['password'])) {
            $moduleConfig['completed'] = false;
        } else {
            $moduleConfig['completed'] = $configsCompleted;
        }

        $event->setArgument('paypal.module.config', $moduleConfig);

    }
}