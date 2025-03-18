<?php

namespace JDS\Http\Generators;

use JDS\Routing\BreadcrumbGeneratorInterface;
use League\Container\Container;

class BreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
	public function __construct(private readonly array $routes, private readonly string $routePrefix)
	{
	}

    public function generateBreadcrumbs(): array
    {
        // $path starts out from here
        $path = $this->getURI(); // Get the current URI (e.g., /pec/roles)
        $breadcrumbs = [];       // Initialize breadcrumbs array

        // check loop while $path !== null
        while ($path !== null) {
            $matched = false; // Reset matched flag

            foreach ($this->routes['metadata'] as $route) {
                // Only consider GET routes with breadcrumb metadata
                if (empty($route['label'])) {
                    continue;
                }

                // Combine and normalize the route path
                $routePath = $this->mergeAndNormalizeRoutePath($this->routePrefix, $route['uri']);

                // Check if the current path matches the route path
                if ($routePath === $path) {
                    // Add breadcrumb with label and full path
                    array_unshift($breadcrumbs, [
                        'label' => $route['label'],
                        'path'  => $routePath
                    ]);

                    // Update `$path` to the parent path, ensuring it's normalized or null
                    $path = ($route['path'] !== null)
                        ? $this->mergeAndNormalizeRoutePath($this->routePrefix, $route['path'])
                        : null;

                    $matched = true;
                    break; // Stop loop once a match is found
                }
            }

            // Stop processing if no match was found for current path
            if (!$matched) {
                break;
            }
        }

        return $breadcrumbs; // Return finalized breadcrumbs
    }

    private function getURI(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $cleanUri = strtok($uri, '?');
        return $cleanUri !== false ? rawurldecode($cleanUri) : '';
    }

    private function mergeAndNormalizeRoutePath(string $routePath, string $route): string
    {
        // Normalize the routePath
        $routePath = trim($routePath, '/') !== '' ? '/' . trim($routePath, '/') : '';

        // Normalize and concatenate with the given route
        $normalizeRoute = rtrim($routePath . '/' . ltrim(trim($route, '/'), '/'), '/');
        return ($route === '/' ? $normalizeRoute . '/' : $normalizeRoute);
    }
}

