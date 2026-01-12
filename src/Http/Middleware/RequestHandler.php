<?php

namespace JDS\Http\Middleware;


use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ControllerDispatcherInterface $controllerDispatcher
    ) {}

    public function handle(Request $request): Response
    {
        return $this->controllerDispatcher->dispatch($request);
    }
}


