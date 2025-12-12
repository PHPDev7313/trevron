<?php

namespace JDS\Http;

use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use Throwable;

final class RouteDispatcher implements RequestHandlerInterface
{
    public function __construct(
        private ControllerDispatcher  $controllerDispatcher,
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            //
            // NEW: Dispatch through ControllerDispatcher
            //
            // ALL real controller logic is performed here
            //
            return $this->controllerDispatcher->dispatch($request);
        } catch (StatusException $e) {
            // Re-throw framework exception untouched
            throw $e;
        } catch (Throwable $e) {

            //
            // Convert unexpected internal failure into a structured StatusException
            //
            throw new StatusException(
                StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                "Route dispatch failed: {$e->getMessage()}. [Route:Dispatcher].",
                $e
            );
        }
    }
}

