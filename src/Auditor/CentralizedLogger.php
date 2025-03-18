<?php

namespace JDS\Auditor;

use Psr\Log\LoggerInterface;

class CentralizedLogger
{


    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Log an event with structured context information.
     *
     * @param string $level     The log level (e.g., INFO, ERROR)
     * @param string $eventType The type of the event (e.g., LOGIN, DELETE, UPDATE)
     * @param array $context    Additional context for the log
     * @return void
     */
    public function log(string $level, string $eventType, array $context = []): void
    {
        // Automatically include a timestamp if not in the context
        if (!isset($context['timestamp'])) {
            $context['timestamp'] = date('Y-m-d H:i:s');
        }

        // Create a standard log message based on the event type
        $message = strtoupper($eventType);

        // Log the message with the structured context
        $this->logger->log($level, $message, $context);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

}


