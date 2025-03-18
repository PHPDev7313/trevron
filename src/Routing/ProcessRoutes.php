<?php

namespace JDS\Routing;

class ProcessRoutes
{
    public static function process(array $routes): array
    {
        $separateRoutes = [];

        $metadataList = [];

        foreach ($routes as $route) {
            $currentMetadata = [];

            // check if the route has metadata in the expected position (index2[3])
            if (isset($route[2][3])) {
                // extract metadata and link back to the route URI (index 1)
                $currentMetadata = array_merge(['uri' => $route[1]], $route[2][3]);
                $metadataList[] = $currentMetadata;
                // remove metadata from the original route
                unset($route[2][3]);
            }
            $separateRoutes[] = $route;
        }
        return [
            'routes' => $separateRoutes,
            'metadata' => $metadataList,
        ];

    }
}