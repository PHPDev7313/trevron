<?php

namespace JDS\Error;

use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionLogger
{
    public function __construct(
        private LoggerInterface      $logger,
        private bool                 $isProduction = true
    )
    {}

    /**
     * Logs a message with the specified code, details, and log level.
     *
     * @param int $code The error or status code to be logged.
     * @param string|null $details Optional additional details related to the log entry.
     * @param string $level The log level at which the message should be logged. Defaults to 'error'.
     * @return string The final formatted log message (without stack trace).
     */

    public function log(
        StatusCode $statusCode,
        ?string $details=null,
        string $level='error',
        ?Throwable $exception=null
    ): string
    {
        $base = $statusCode->formatted(); // "[4100] Database Error: Migration apply failed"

        $message = $details
            ? sprintf('%s | Details: %s', $base, $details)
            : $base;

        // Production: no trace printed - only structured exception context
        $context = [];

        if ($exception) {
            // Let Monolog do the heavy lifting for stack traces
            $context['exception'] = $exception;
        }

        $this->logger->log($level, $message, $context);

        return $message;
    }
}



//
//
//
//        // get the error message for the given code
//        $message = $this->statusCodeManager->getMessage($code);
//
//        // format the log message
//        $formattedMessage = sprintf(
//            "%s%s%s",
//            $message,
//            $details ? " | Details: {$details}" : "",
//            (!$this->isProduction ? ($exception ? sprintf(" | Exception: %s", ExceptionFormatter::formatTrace($exception)) : "") : "")
//        );
//
//        // log the final message at the appropriate level
//        $this->logger->log($level, $formattedMessage);
//        return $formattedMessage;
