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

namespace JDS\Http\Middleware;

use FastRoute\Dispatcher;
use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Exceptions\Http\HttpException;
use JDS\Exceptions\Http\HttpRequestMethodException;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Routing\Route;
use Throwable;

final class ExtractRouteInfo implements MiddlewareInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    )
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        try {
            $routeInfo = $this->dispatcher->dispatch(
                $request->getMethod(),
                $request->getPathInfo()
            );
        } catch (Throwable $e) {
            //
            // Infrastructure failure, not a routing miss
            //
            throw new StatusException(
                StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                "Routing infrastructure failure.",
                $e
            );
        }

        return match ($routeInfo[0]) {

            Dispatcher::FOUND =>
            $this->handleFoundRoute($routeInfo, $request, $next),

            Dispatcher::METHOD_NOT_ALLOWED =>
                throw new HttpRequestMethodException(
                    "Method not allowed.",
                    405
                ),

            Dispatcher::NOT_FOUND =>
            throw new HttpException('Not Found', 404),

            default =>
                throw new StatusException(
                    StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                    'Unknown routing dispatch result.'
                ),
        };
    }

    private function handleFoundRoute(
        array $routeInfo,
        Request $request,
        RequestHandlerInterface $next
    ): Response
    {
        /** @var Route $route */
        $route = $routeInfo[1];
        $vars = $routeInfo[2] ?? []; // dynamic parameters

        //
        // Attach route to Request
        //
        $request->setRoute($route);
        $request->setRouteParams($vars);

        return $next->handle($request);
    }
}



//    private function normalizeRoute(string $route): string
//    {
//        $route = trim($route);
//
//        if ($route === '' || $route === '/') {
//            return '/';
//        }
//
//        return '/' . trim($route, '/');
//    }




////
//// Build FastRoute dispatcher
////
//$dispatcher = simpleDispatcher(function (RouteCollector $collector) {
//    foreach ($this->routes['routes'] as $route) {
//
//        //
//        // Normalize path
//        //
//        $fullPath = $this->normalizeRoute($route->getPath());
//        //
//        // Register with FastRoute
//        //
//        $collector->addRoute(
//                   $route->getMethod(),
//                   $fullPath,
//                   $route // store Route object as handler
//        );
//    }
//});
//
