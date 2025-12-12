<?php

namespace Tests\Stubs\EventDispatcher;

use JDS\EventDispatcher\Event;

class FakeStoppableEvent extends Event
{
    public array $log = [];
}

