<?php

namespace JDS\Http\EventSubscriber;

use JDS\Auditor\CentralizedLogger;
use JDS\Contracts\Events\EventSubscriberInterface;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use Throwable;

final class HttpKernelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CentralizedLogger $logger
    ) {}

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => ['onResponse', 0],
            TerminateEvent::class => ['onTerminate', -100],
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        //
        // mutate response, headers, logging, etc.
        //
        $this->logger->info('Response dispatched');
    }

    /**
     * MUST NEVER THROW
     */
    public function onTerminate(TerminateEvent $event): void
    {
        try {
            $this->logger->debug('Request terminated', [
                'duration' => $event->getDuration(),
            ]);
        } catch (Throwable) {
            // swallow EVERYTHING - terminate phase is sacred
        }
    }
}


