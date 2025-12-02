<?php

namespace JDS\Contracts\Events;

interface EventSubscriberInterface
{
    /**
     * Returns [
     *      EventClassName::class => [ listenerMethod, priority? ]
     * ]
     */
    public static function getSubscribedEvents(): array;
}

