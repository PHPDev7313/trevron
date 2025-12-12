<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Contracts\Routing\RouteMiddlewareAwareInterface;
use JDS\Http\Request;
use Psr\Container\ContainerInterface;

class DynamicMiddlewareResolver implements MiddlewareResolverInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        /** @var class-string<MiddlewareInterface>[] */
        private readonly array $globalMiddleware = []
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewareForRequest(Request $request): array
    {
        $route = $request->getRoute(); // must exist on Request

        $middleware = [];

        //
        // 1. Add global middleware first
        //
        foreach ($this->globalMiddleware as $class) {
            $middleware[] = $this->container->get($class);
        }

        //
        // 2. Add route-level middleware next
        //
        if ($route instanceof RouteMiddlewareAwareInterface) {
            foreach ($route->getMiddleware() as $class) {
                $middleware[] = $this->container->get($class);
            }
        }

        return $middleware;
    }
}

