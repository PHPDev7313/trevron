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

class RouteCollection
{
    /** @var Route[] */
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /** @return Route[] */
    public function all(): array
    {
        return $this->routes;
    }
}

