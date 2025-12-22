<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

namespace JDS\Routing;

use JDS\Http\Request;

final class RouteMatcher
{
    public function __construct(
        private array $routes // ProcessRoutes['routes]
    )
    {
    }

    public function match(Request $request): ?Route
    {
        $method = $request->getMethod();
        $path = $request->getPathInfo();

        foreach ($this->routes as [$httpMethod, $uri, $handler]) {
            if ($httpMethod !== $method) {
                continue;
            }

            if ($uri === $path) {
                return new Route($httpMethod, $uri, $handler);
            }
        }
        return null;
    }
}

