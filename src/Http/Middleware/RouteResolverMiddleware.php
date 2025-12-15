<?php

namespace JDS\Http\Middleware;



use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
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

        if ($route !== null) {
            $request->setRoute($route);

            //
            // Optional but recommended
            //
            $request = $request->withAttribute(
                'route.middleware',
                $route->getMiddleware()
            );
        }
        return $next->handle($request);
    }
}

