<?php

namespace JDS\Logging;

use JDS\Handlers\ExceptionFormatter;
use JDS\Http\StatusCodeManager;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionLogger
{
    public function __construct(
        private LoggerInterface $logger,
        private StatusCodeManager $statusCodeManager,
        private bool $isProduction = true)
    {
    }

    /**
     * Logs a message with the specified code, details, and log level.
     *
     * @param int $code The error or status code to be logged.
     * @param string|null $details Optional additional details related to the log entry.
     * @param string $level The log level at which the message should be logged. Defaults to 'error'.
     * @return void
     */

    public function log(int $code, ?string $details=null, string $level='error', ?Throwable $exception=null): string
    {
        // get the error message for the given code
        $message = $this->statusCodeManager->getMessage($code);

        // format the log message
        $formattedMessage = sprintf(
            "%s%s%s",
            $message,
            $details ? " | Details: {$details}" : "",
            (!$this->isProduction ? ($exception ? sprintf(" | Exception: %s", ExceptionFormatter::formatTrace($exception)) : "") : "")
        );

        // log the final message at the appropriate level
        $this->logger->log($level, $formattedMessage);
        return $formattedMessage;
    }
}

