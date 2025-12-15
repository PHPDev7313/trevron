<?php

namespace JDS\Exceptions\Routing;

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use Throwable;

class RouteNotFoundException extends StatusException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct(StatusCode::HTTP_ROUTE_NOT_FOUND, "No route found for {$method} {$path}.");
    }
}

