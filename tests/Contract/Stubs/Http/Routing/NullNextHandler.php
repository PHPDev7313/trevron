<?php

namespace Tests\Contract\Stubs\Http\Routing;

use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

final class NullNextHandler implements RequestHandlerInterface
{

    public function handle(Request $request): Response
    {
        return new Response('next', 200);
    }
}