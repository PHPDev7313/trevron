<?php

namespace Tests\Stubs\Events;

use JDS\Contracts\Events\EventSubscriberInterface;

final class LowPrioritySubscriber implements EventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => ['handle', -100],
        ];
    }

    public function handle(TestEvent $event): void
    {
        $event->calls[] = 'low';
    }
}

