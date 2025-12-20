<?php

namespace Tests\Contract\Stubs\Http\Kernel;

use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Http\Request;

final class FakeMiddlewareResolver implements MiddlewareResolverInterface
{

    /**
     * @inheritDoc
     */
    public function getMiddlewareForRequest(Request $request): array
    {
        return [];
    }
}

