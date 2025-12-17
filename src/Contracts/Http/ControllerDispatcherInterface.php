<?php

namespace JDS\Contracts\Http;

use JDS\Http\Request;
use JDS\Http\Response;

interface ControllerDispatcherInterface
{
    public function dispatch(Request $request): Response;
}

