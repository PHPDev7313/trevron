<?php

namespace JDS\Error;

enum StatusCategory: int
{
    case Server         = 500;
    case Containers     = 2100;
    case Controllers    = 2200;
    case Authentication = 2300;
    case Entity         = 2400;
    case Enum           = 2500;
    case EventListener  = 2600;
    case Form           = 2700;
    case Logging        = 2800;
    case Middleware     = 2900;
    case Provider       = 3000;
    case Repository     = 3100;
    case Security       = 3200;
    case Template       = 3300;
    case Traits         = 3400;
    case Console        = 3500;
    case ConsoleKernel  = 3600;
    case Http           = 3800;
    case HttpKernel     = 3900;
    case Database       = 4100;
    case Mail           = 4200;
    case FileSystem     = 4300;
    case Json           = 4400;
    case Image          = 4500;

    /**
     * Reverse lookup from numeric code to category
     */
    public static function fromCode(int $code): self
    {
        foreach (self::cases() as $case) {
            $base = $case->value;

            $rangeSize = match ($case) {
                self::ConsoleKernel => 200,
                self::HttpKernel => 200,
                default => 100,
            };

            if ($code >= $base && $code < $base + $rangeSize) {
                return $case;
            }
        }

        return self::Server; // safe default for unknown category
    }
}

