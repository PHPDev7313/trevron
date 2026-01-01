<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Routing\LockableRouterInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

class RoutingPhase implements BootstrapPhaseInterface
{
    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::ROUTING;
    }

    public function bootstrap(Container $container): void
    {
        if (!$container->has(LockableRouterInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Routing bootstrap missing."
            );
        }

        $router = $container->get(LockableRouterInterface::class);
        $router->lock();
    }
}

