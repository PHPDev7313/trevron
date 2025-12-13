<?php

namespace Tests\Stubs\Events;

use JDS\EventDispatcher\Event;
use Psr\EventDispatcher\StoppableEventInterface;

final class TestEvent extends Event implements StoppableEventInterface
{
    public array $calls = [];
}

