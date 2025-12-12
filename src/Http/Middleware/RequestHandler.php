<?php

namespace JDS\Http\Middleware;


use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class RequestHandler implements RequestHandlerInterface
{

    public function handle(Request $request): Response
    {
        // TODO: Implement handle() method.
    }
}


