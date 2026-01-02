<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

class CommandPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::COMMANDS;
    }

    public function bootstrap(Container $container): void
    {
        if (!$container->has(CommandRegistryInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Command Registry Interface is missing during COMMAND phase. [Command:Phase]."
            );
        }

        $registry = $container->get(CommandRegistryInterface::class);

        if (method_exists($registry, 'lock')) {
            $registry->lock();
        }
    }

}

