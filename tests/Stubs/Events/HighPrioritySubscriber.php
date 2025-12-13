<?php

namespace Tests\Stubs\Events;

use JDS\Contracts\Events\EventSubscriberInterface;

class HighPrioritySubscriber implements EventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    final public static function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => ['handle', 100],
        ];
    }

    public function handle(TestEvent $event): void
    {
        $event->calls[] = 'high';
    }
}

