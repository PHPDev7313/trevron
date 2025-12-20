<?php

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

