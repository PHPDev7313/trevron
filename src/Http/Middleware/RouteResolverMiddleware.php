<?php

namespace JDS\Http\Middleware;



use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Exceptions\Routing\RouteNotFoundException;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Routing\RouteMatcher;

class RouteResolverMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteMatcher $matcher
    )
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $route = $this->matcher->match($request);

        if ($route === null) {
            throw new RouteNotFoundException(
                $request->getMethod(),
                $request->getPathInfo()
            );
        }

        //
        // Optional but recommended
        //
        $request = $request
            ->withRoute($route)
            ->withAttribute(
                'route.middleware',
                $route->getMiddleware()
        );

        return $next->handle($request);
    }
}

