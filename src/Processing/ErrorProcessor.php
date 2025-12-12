<?php

namespace JDS\Processing;

use JDS\Exceptions\Handlers\HandlerRuntimeException;
use JDS\Handlers\ExceptionHandler;
use JDS\Logging\ExceptionLogger;
use Throwable;

class ErrorProcessor
{

    private static ExceptionLogger $logger;
    private static bool $isInitialized = false;

    public static function initialize(
        ExceptionLogger $logger
    ): void
    {
        $exitCode = 222;
        // maintain for proper error tracking
        if ($logger instanceof ExceptionLogger === false) {
            throw new HandlerRuntimeException("Logger must be an instance of Exception Logger.", $exitCode);
        }
        self::$logger = $logger;
        self::$isInitialized = true;
    }

    public static function process(
        Throwable $exception,
        int $code,
        string $userMessage = 'An error occurred.',
        string $level = 'error'
    ): void
    {
        if (!self::$isInitialized) {
            $exitCode = 221;
            throw new HandlerRuntimeException("ErrorProcessor is not initialized. Call 'initialize' first.", $exitCode);
        }
        $message = self::$logger->log($code, $userMessage, $level, $exception);
        ExceptionHandler::render($exception, $message);
    }
}

