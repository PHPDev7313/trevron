<?php

namespace JDS\Handlers;

use Throwable;

class ExceptionFormatter
{
    public static function formatTrace(Throwable $exception): string
    {
        // get the trace as an array
        $trace = $exception->getTrace();
        $formatted = [];
        foreach ($trace as $index => $frame) {
            $formatted[] = sprintf(
              "#%d %s(%s): %s%s%s",
              $index,
              $frame['file'] ?? '[internal function]',
              $frame['line'] ?? '',
              $frame['class'] ?? '',
              $frame['type'] ?? '',
              $frame['function'] ?? ''
            );
        }
        // join the array into a human-readable string with newlines
        return implode("\n", $formatted);
    }
}

