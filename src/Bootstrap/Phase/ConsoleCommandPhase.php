<?php

namespace JDS\Bootstrap\Phase;

use JDS\Console\CommandRegistry;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class ConsoleCommandPhase implements BootstrapPhaseInterface
{

    public function bootstrap(Container $container): void
    {
        $container->addShared(CommandRegistry::class);
    }
}

