<?php

namespace JDS\Routing;

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

class ProcessRoutes
{
    public static function process(array $routes): array
    {
        $metadataList = [];

        foreach ($routes as $route) {

            //
            // Validate base route structure
            //
            if (!isset($route[0], $route[1], $route[2])) {
                throw new StatusException(
                    StatusCode::ROUTE_METADATA_INVALID,
                    "Invalid route definition: missing method, path, or controller."
                );
            }

            $method = $route[0];
            $uri = self::normalizeUri($route[1]);
            $controllerInfo = $route[2];

            //
            // We expect controller info: [controllerClass, methodName, middleware?, metadata?]
            //
            $controllerClass = $controllerInfo[0] ?? null;
            $controllerMethod = $controllerInfo[1] ?? null;
            $middleware = $controllerInfo[2] ?? [];
            $rawMetadata = $controllerInfo[3] ?? null;

            //
            // Turn metadata array into validated RouteMetadata object
            //
            $metadataObject = null;

            if (is_array($rawMetadata)) {
                $metadataObject = RouteMetadata::fromArray($rawMetadata);

                //
                // Build metadata entry for the global metadata list
                //
                $metadataList[] = [
                    'uri' => $uri,
                    'label' => $metadataObject->label,
                    'path' => $metadataObject->path,
                    'requires_token' => $metadataObject->requiresToken,
                ];
            }

            //
            // Reconstruct cleaned controller info
            //
            $cleanControllerInfo = [
                $controllerClass,
                $controllerMethod,
                $middleware,
            ];

            //
            // If metadata existed, append the validated object
            //
            if ($metadataObject !== null) {
                $cleanControllerInfo[] = $metadataObject;
            }

            //
            // Add final route entry
            //
            $processedRoutes[] = [
                $method,
                $uri,
                $cleanControllerInfo,
            ];
        }
        return [
            'routes' => $processedRoutes,
            'metadata' => $metadataList,
        ];
    }
    private static function normalizeUri(string $uri): string
    {
        $uri = trim($uri);

        if ($uri === '' || $uri === '/') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }
}

