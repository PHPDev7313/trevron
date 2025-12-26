<?php

namespace JDS\Bootstrap;

use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Events\EventDispatcherInterface;
use JDS\EventDispatcher\EventDispatcher;
use League\Container\Container;

class CoreServicePhase implements BootstrapPhaseInterface
{

    public function bootstrap(Container $container): void
    {
        $container->addShared(EventDispatcher::class);
        $container->addShared(
            EventDispatcherInterface::class,
            EventDispatcher::class
        );
    }
}

