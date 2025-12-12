<?php

namespace JDS\Contracts\Routing;

use JDS\Contracts\Middleware\MiddlewareInterface;

interface RouteMiddlewareAwareInterface
{
    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddleware(): array;
}

