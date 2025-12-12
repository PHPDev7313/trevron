<?php

namespace Tests\Stubs\Http\Kernel;

use Symfony\Component\EventDispatcher\EventDispatcher;

class FakeEventDispatcher extends EventDispatcher
{
    public array $events = [];

    public function dispatch(object $event): void
    {
        $this->events[] = get_class($event);
    }
}