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

namespace JDS\Processing;

use JDS\Error\StatusCode;
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
        // maintain for proper error tracking
        if ($logger instanceof ExceptionLogger === false) {
            $exitCode = StatusCode::CONSOLE_KERNEL_INVALID_LOGGER_INSTANCE; // 222
            throw new HandlerRuntimeException("Logger must be an instance of Exception Logger.", $exitCode->value);
        }
        self::$logger = $logger;
        self::$isInitialized = true;
    }

    public static function process(
        Throwable $exception,
        StatusCode $code,
        string $userMessage = 'An error occurred.',
        string $level = 'error'
    ): void
    {
        if (!self::$isInitialized) {
            $exitCode = StatusCode::CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED; // 221
            throw new HandlerRuntimeException("ErrorProcessor is not initialized. Call 'initialize' first.", $exitCode->value);
        }
        $message = self::$logger->log($code->value, $userMessage, $level, $exception);
        ExceptionHandler::render($exception, $message);
    }
}

