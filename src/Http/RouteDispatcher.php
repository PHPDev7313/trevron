<?php

namespace JDS\Http;

use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\ErrorProcessor;
use JDS\Error\StatusCode;
use JDS\Error\StatusException;
use Throwable;

final class RouteDispatcher implements RequestHandlerInterface
{

    public function handle(Request $request): Response
    {
        try {
            $handler = $request->getRouteHandler();
            $args = $request->getRouteHandlerArgs() ?? [];

            $response = call_user_func_array($handler, $args);

            if (!($response instanceof Response))  {
                throw new StatusException(
                    StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                    "Route handler did not return a Response object."
                );
            }
            return $response;
        } catch (Throwable $e) {
            ErrorProcessor::process(
                $e,
                StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                "Route dispatch failed."
            );

            return new Response("Internal Server Error", 500);
        }
    }
}

