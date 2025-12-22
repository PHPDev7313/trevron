<?php

namespace Tests\Contract\Stubs\Http\Kernel;

use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Http\Request;
use JDS\Http\Response;

final class SuccessfulControllerDispatcher implements ControllerDispatcherInterface
{

    public function dispatch(Request $request): Response
    {
        return new Response('OK', 200);
    }
}