<?php

namespace JDS\Error;

use JDS\Exceptions\Error\StatusException;
use JDS\Handlers\ExceptionHandler;
use Throwable;

class ErrorProcessor
{

    private static ?ExceptionLogger $logger = null;

    public static function initialize(
        ExceptionLogger $logger
    ): void
    {
        self::$logger = $logger;
    }

    /**
     * FOR TESTING ONLY - resets processor to uninitialized state.
     */
    public static function reset(): void
    {
        self::$logger = null;
    }

    public static function process(
        Throwable $exception,
        StatusCode $statusCode,
        ?string $userMessage = null,
        string $level = 'error'
    ): void
    {
        if (self::$logger === null) {
            //
            // hard fail - this is framework boot misconfiguration
            throw new StatusException(
                StatusCode::CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED,
                "ErrorProcessor is not initialized. Call ErrorProcessor::initialize() first. [Error:Processor].",
                $exception
            );
        }

        $message = $userMessage ?? $statusCode->defaultMessage();

        $loggedMessage = self::$logger->log(
            $statusCode,
            $message,
            $level,
            $exception
        );

        // Let your existing handler render the error as appropriate
        // (CLI, HTTP, etc.). You can extend this signature later to
        // pass StatusCode if you wish.
        ExceptionHandler::render($exception, $loggedMessage);
    }
}

