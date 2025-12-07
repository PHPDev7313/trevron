<?php

namespace JDS\Error;

use Throwable;

class ExceptionFormatter
{

    public static function format(Throwable $exception): string
    {
        $output = [];

        do {
            $output[] = sprintf(
                "Exception: %s\nMessage: %s\nFile: %s:%d\nTrace:",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );

            $output[] = self::formatTrace($exception->getTrace());
        } while ($exception = $exception->getPrevious());

        return implode("\n", $output);
    }

    public static function formatTrace(array $trace): string
    {
        $lines = [];

        foreach ($trace as $i => $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? '';
            $class = $frame['class'] ?? '';
            $type = $frame['type'] ?? '';
            $function = $frame['function'] ?? '';

            $lines[] = sprintf(
                "#%d %s(%s): %s%s%s",
                $i,
                $file,
                $line,
                $class,
                $type,
                $function
            );
        }
        return implode("\n", $lines);
    }
}



