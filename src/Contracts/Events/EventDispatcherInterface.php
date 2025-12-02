<?php

namespace JDS\Contracts\Events;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Attach a listener to a given event
     */
    public function addListener(string $eventName, callable $listener): self;

    /**
     * Dispatch an event to all registered listeners
     */
    public function dispatch(object $event): object;
}

