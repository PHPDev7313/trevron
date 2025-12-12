<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class RequiredAccessLevel implements MiddlewareInterface
{
    public function __construct(private SessionInterface $session) {}

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $identity = $this->session->get('auth_identity');

        //
        // strore identity on the request (Request should support attributes)
    }
}

