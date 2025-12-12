<?php

namespace Tests\Stubs\Fakes;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class FakeMiddleware implements MiddlewareInterface
{
    public static array $order = [];

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        self::$order[] = 'before';
        $response = $next->handle($request);
        self::$order[] = 'after';
        return $response;
    }
}


