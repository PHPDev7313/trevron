<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\EventDispatcher\EventDispatcher;

final class NullEventDispatcher extends EventDispatcher
{
    public function dispatch(object $event): object
    {
        return $event;
    }
}

