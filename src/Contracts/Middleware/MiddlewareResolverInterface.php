<?php

namespace JDS\Contracts\Middleware;

use JDS\Http\Request;

interface MiddlewareResolverInterface
{
    /** @return MiddlewareInterface[] */
    public function getMiddlewareForRequest(Request $request): array;
}

