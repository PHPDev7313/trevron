<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class FakeMiddleware2 implements MiddlewareInterface
{
    public array $log = [];
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $this->log[] = "m2-before";
        $response = $next->handle($request);
        $this->log[] = "m2-after";

        return $response;
    }
}

