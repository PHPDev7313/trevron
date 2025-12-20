<?php

namespace Tests\Contract\Stubs\Http\Kernel;

use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Request;
use JDS\Http\Response;

final class StatusExceptionThrowingDispatcher implements ControllerDispatcherInterface
{

    public function dispatch(Request $request): Response
    {
        throw new StatusException(
            StatusCode::HTTP_ROUTE_NOT_FOUND,
            'Route not found'
        );
    }
}

