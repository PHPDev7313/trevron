<?php

namespace JDS\Error;

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




// }

//        $exitCode = 222;
//        // maintain for proper error tracking
//        if ($logger instanceof ExceptionLogger === false) {
//            throw new HandlerRuntimeException("Logger must be an instance of Exception Logger.", $exitCode);
//        }
//        self::$logger = $logger;
//        self::$isInitialized = true;
//
//
//public static function process(
//        Throwable $exception,
//        int $code,
//        string $userMessage = 'An error occurred.',
//        string $level = 'error'
//    ): void
//    {
//        if (!self::$isInitialized) {
//            $exitCode = 221;
//            throw new HandlerRuntimeException("ErrorProcessor is not initialized. Call 'initialize' first.", $exitCode);
//        }
//        $message = self::$logger->log($code, $userMessage, $level, $exception);
//        ExceptionHandler::render($exception, $message);
//    }




