<?php

namespace JDS\Logging;

use Psr\Log\LoggerInterface;

class ActivityLogger
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Logs a message with a specified log level.
     *
     * @param string $message The message to be logged.
     * @param string $level The log level for the message. Default is 'info'.
     * @return void
     */
    public function track(string $message, string $level = 'info'): void
    {
        $this->logger->log($level, $message);
    }

}

