<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 5, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

namespace JDS\Logging;

use JDS\Handlers\ExceptionFormatter;
use JDS\Http\StatusCodeManager;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionLogger
{
    public function __construct(
        private LoggerInterface   $logger,
        private StatusCodeManager $statusCodeManager,
        private bool              $isProduction = true)
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

