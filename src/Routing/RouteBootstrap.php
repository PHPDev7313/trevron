<?php
/*
 * Trevron Framework - v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 22, 2025
 *
 * see: RouteingARCHITECTURE.v1.2.FINAL.md
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

