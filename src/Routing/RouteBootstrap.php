<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 27, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 */

namespace JDS\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

final class RouteBootstrap
{
    public static function buildDispatcher(ProcessedRoutes $processed): Dispatcher
    {
        return simpleDispatcher(
            function (RouteCollector $collector) use ($processed): void {
                foreach ($processed->routes->all() as $route) {
                    $collector->addRoute(
                        $route->getMethod(),
                        $route->getPath(),
                        $route
                    );
                }
            }
        );
    }
}

