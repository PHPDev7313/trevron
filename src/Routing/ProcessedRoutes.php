<?php

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


