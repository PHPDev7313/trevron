<?php

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

