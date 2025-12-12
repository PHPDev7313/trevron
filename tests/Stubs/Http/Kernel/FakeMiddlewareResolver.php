<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Http\Request;

class FakeMiddlewareResolver implements MiddlewareResolverInterface
{

    /** @var array<class-string> */
    private array $middleware;

    public function __construct(array $middleware=[])
    {
        $this->middleware = $middleware;
    }


    /**
     * @inheritDoc
     */
    public function getMiddlewareForRequest(Request $request): array
    {
        return $this->middleware;
    }
}

