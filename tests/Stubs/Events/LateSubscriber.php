<?php

namespace Tests\Stubs\Events;

use JDS\Contracts\Events\EventSubscriberInterface;

class LateSubscriber implements EventSubscriberInterface
{
    private const int PRIORITY = -100;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => ['handle', self::PRIORITY],
        ];
    }

    public function handle(TestEvent $event): void
    {
        $event->calls[] = 'late';
    }
}

