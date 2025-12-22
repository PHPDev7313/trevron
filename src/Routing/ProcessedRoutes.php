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

use JDS\Http\Navigation\NavigationMetadataCollection;

class ProcessedRoutes
{
    public function __construct(
        public readonly RouteCollection $routes,
        public readonly NavigationMetadataCollection $metadata
    )
    {
    }
}


