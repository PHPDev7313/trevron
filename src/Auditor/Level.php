<?php

namespace JDS\Auditor;

use JDS\Auditor\Exception\InvalidArgumentException;
use JDS\Processing\ErrorProcessor;

class Level
{
    // Define allowed levels as constants
    private const VALID_LEVELS = [
        'INFO',
        'WARNING',
        'ERROR',
        'DEBUG',
        'CRITICAL',
        'ALERT',
        'NOTICE',
    ];

    private string $level;

    public function __construct(string $level)
    {
        // Normalize the level (e.g., convert to uppercase)
        $normalizedLevel = strtoupper($level);

        // Validate the level
        if (!in_array($normalizedLevel, self::VALID_LEVELS, true)) {
            $exitCode = 213;
            ErrorProcessor::process(
                new InvalidArgumentException(
                sprintf('Invalid log level: %s. Valid levels are: %s', $level, implode(', ', self::VALID_LEVELS))),
                $exitCode,
                sprintf("Invalid log level! Valid levels are: %s", implode(', ', self::VALID_LEVELS))
            );
            exit($exitCode);
        }

        $this->level = $normalizedLevel;
    }

    /**
     * Get the normalized log level as a string.
     */
    public function __toString(): string
    {
        return $this->level;
    }

    /**
     * Get all valid levels (for use in JSON validation, etc.).
     */
    public static function getValidLevels(): array
    {
        return self::VALID_LEVELS;
    }

}

