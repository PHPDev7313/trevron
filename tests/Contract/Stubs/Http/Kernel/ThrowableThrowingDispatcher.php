<?php

namespace Tests\Contract\Stubs\Http\Kernel;

use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Http\Request;
use JDS\Http\Response;
use RuntimeException;

final class ThrowableThrowingDispatcher implements ControllerDispatcherInterface
{

    public function dispatch(Request $request): Response
    {
        throw new RuntimeException('boom');
    }
}

