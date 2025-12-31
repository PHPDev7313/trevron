<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use League\Container\Container;

class CommandPhase implements BootstrapPhaseInterface
{

    public function phase(): BoostrapPhase
    {
        return BoostrapPhase::COMMANDS;
    }

    public function bootstrap(Container $container): void
    {
        if (!$container->has(CommandRegistryInterface::class)) {
            return;
        }

        $registry = $container->get(CommandRegistryInterface::class);

        if (method_exists($registry, 'lock')) {
            $registry->lock();
        }
    }

}