<?php

namespace JDS\Contracts\Bootstrap;

use FastRoute\Dispatcher;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Routing\ProcessedRoutes;
use JDS\Routing\RouteBootstrap;
use League\Container\Container;

class RoutingBootstrap implements BootstrapPhaseInterface
{
    public function __construct(
        private readonly ProcessedRoutes $routes
    ) {}

    public function bootstrap(Container $container): void
    {
        $dispatcher = RouteBootstrap::buildDispatcher($this->routes);

        $container->add(Dispatcher::clss, $dispatcher)
            ->setShared(true);

        $container->add(ExtractRouteInfo::class)
            ->addArgument(Dispatcher::class)
            ->setShared(true);
    }
}

