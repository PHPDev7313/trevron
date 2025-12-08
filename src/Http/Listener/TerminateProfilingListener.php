<?php

namespace JDS\Http\Listener;

use JDS\Error\ExceptionHandler;
use JDS\Http\Event\TerminateEvent;

final class TerminateProfilingListener
{
    public function __invoke(TerminateEvent $event): void
    {
        if (!ExceptionHandler::isDebug()) {
            return;
        }

        $duration = round($event->getDuration() * 1000, 2);

        echo "\n--- Profiling ---\n";
        echo "Duration: {$duration} ms\n";
        echo "Memory: " . memory_get_usage(true) . "bytes\n";
        echo "Peak: " . memory_get_peak_usage(true) . "bytes\n";
    }
}

