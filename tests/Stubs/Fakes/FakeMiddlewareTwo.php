<?php

namespace Tests\Stubs\Fakes;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class FakeMiddlewareTwo implements MiddlewareInterface
{

    public static array $order = [];


    public function process(Request $request, RequestHandlerInterface $requestHandler): Response
    {
        self::$order[] = 'two-before';
        $response = $requestHandler->handle($request);
        self::$order[] = 'two-after';

        return $response;
    }
}

