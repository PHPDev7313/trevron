<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Routing\RouteBootstrap;
use League\Container\Container;

class RoutingPhase implements BootstrapPhaseInterface
{
    public function __construct(
        private readonly RouteBootstrap $bootstrap
    ) {}

    public function bootstrap(Container $container): void
    {
        $dispatcher = $this->bootstrap->buildDispatcher();

        $container->addShared(
            ExtractRouteInfo::class,
            fn () => new ExtractRouteInfo($dispatcher)
        );
    }
}

