<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class FakeMiddleware implements MiddlewareInterface
{

    public array $log = [];

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $this->log[] = "before";
        $response = $next->handle($request);
        $this->log[] = "after";

        return $response;
    }
}

