<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 23, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapARCHITECTURE.v1.2.FINAL.md
 */

namespace JDS\Bootstrap;

use FastRoute\Dispatcher;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Routing\ProcessedRoutes;
use JDS\Routing\RouteBootstrap;
use League\Container\Container;

final class RoutingBootstrap implements BootstrapPhaseInterface
{
    public function __construct(
        private readonly ProcessedRoutes $routes
    ) {}

    public function bootstrap(Container $container): void
    {
        $dispatcher = RouteBootstrap::buildDispatcher($this->routes);

        $container->add(Dispatcher::class)
            ->addArgument(
                RouteBootstrap::buildDispatcher($this->routes)
            )
            ->setShared(true);

        $container->add(ExtractRouteInfo::class)
            ->addArgument(Dispatcher::class)
            ->setShared(true);
    }
}

