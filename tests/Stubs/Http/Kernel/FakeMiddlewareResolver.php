<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Http\Request;

class FakeMiddlewareResolver implements MiddlewareResolverInterface
{

    /**
     * @inheritDoc
     */
    public function getMiddlewareForRequest(Request $request): array
    {
        return [];
    }
}