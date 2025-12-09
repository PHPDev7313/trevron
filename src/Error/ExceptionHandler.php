<?php

namespace JDS\Error;

use Throwable;

class ExceptionHandler
{
    /**
     * Debug flag - must be set by the framework/kernel.
     * true  => show full exception + formatted trace
     * false => show safe, user-friendly output only
     */
    private static bool $debug = false;

    public static function initializeWithEnvironment(string $environment): void
    {
        self::$debug = ($environment !== 'development');
        // production !== development
        // development !== development
        // staging !== development
        // testing !== development
    }

    /**
     * Render an exception as an error message for the user.
     * for CLI or HTTP output
     *
     * @param Throwable $exception Original exception object - The exception to render.
     * @param string $safeMessage The message produced by ErrorProcessor/ExceptionLogger
     *
     * @return void
     */
    public static function render(Throwable $exception, string $safeMessage): void
    {
        //--------------------------------------------------------------
        // PRODUCTION MODE: (debug = false) Show generic message only
        //--------------------------------------------------------------
        if (!self::$debug) {
            //
            // Only show the safe message (usually "[4100] Database Error ...")
            echo $safeMessage . PHP_EOL;
            return;
        }

        // -------------------------------------------------------------------
        // DEBUG MODE (developer environment)
        // Show EVERYTHING, using your Pretty Exception Formatter
        // -------------------------------------------------------------------
        echo $safeMessage . PHP_EOL . PHP_EOL;

        echo "=== Exception Details ===" . PHP_EOL;
        echo "Type: " . $exception::class . PHP_EOL;
        echo "Message: " . $exception->getMessage() . PHP_EOL;
        echo "File: " . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;

        echo PHP_EOL . "=== Stack Trace ===" . PHP_EOL;
        echo ExceptionFormatter::format($exception);
        echo PHP_EOL;
    }

    /**
     * Determine whether the application is in production mode.
     *
     * @return bool True if production mode, false if development.
     */
    private static function isProduction(): bool
    {
        return self::$debug;
    }

    /**
     * Format a user-friendly error message.
     *
     * @param string $message The message to display.
     * @return string Formatted user-facing message.
     */
    private static function formatUserMessage(string $message): string
    {
        return "[Error]: " . htmlspecialchars($message, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * Format a detailed error message for development.
     *
     * @param Throwable $exception The exception to format.
     * @param string $userMessage Optional user message.
     * @return string Formatted error message with trace.
     */
    private static function formatDetailedError(Throwable $exception, string $userMessage): string
    {
        return sprintf(
            "<strong>User Message:</strong> %s<br>" .
            "<strong>Exception:</strong> %s<br>" .
            "<strong>File:</strong> %s<br>" .
            "<strong>Line:</strong> %d<br>" .
            "<strong>Trace:</strong><pre>%s</pre>",
            htmlspecialchars($userMessage, ENT_QUOTES | ENT_HTML5),
            htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_HTML5),
            $exception->getFile(),
            $exception->getLine(),
            htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES | ENT_HTML5)
        );
    }

    public static function enableDebug(): void
    {
        self::$debug = true;
    }

    public static function disableDebug(): void
    {
        self::$debug = false;
    }

    public static function isDebug(): bool
    {
        return self::$debug;
    }

}





//        $isProduction = self::isProduction();
//
//        if ($isProduction) {
//            // In production, display only the user-friendly message
//            echo self::formatUserMessage($userMessage) . PHP_EOL;
//        } else {
//            // In development, display detailed information
//            echo nl2br(self::formatDetailedError($exception, $userMessage)) . PHP_EOL;
//        }
//    }
//
//    /**
//     * Render a critical error message and terminate the application.
//     *
//     * @param Throwable $exception The exception to handle.
//     * @param string $userMessage A user-friendly message to display.
//     * @param int $exitCode Exit code (default 1).
//     * @return void
//     */
//    public static function renderCritical(Throwable $exception, string $userMessage = 'Critical error occurred.', int $exitCode = 1): void
//    {
//        self::render($exception, $userMessage);
//        exit($exitCode);
//    }




