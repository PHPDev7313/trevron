<?php

namespace JDS\Contracts\Events;

interface EventListenerInterface
{
    /**
     * Each listener handles exactly one event
     */
    public function __invoke(object $event): void;
}

