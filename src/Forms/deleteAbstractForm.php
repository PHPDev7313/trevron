<?php

namespace JDS\Forms;

use DateTime;
use DateTimeImmutable;

class deleteAbstractForm
{
    protected function generateSlug(string $title): string
    {
        // Convert title to lowercase, trim spaces, replace special characters with hyphens, and remove extra hyphens
        return preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($title))));
    }

    protected function toBoolean(mixed $value): ?bool {
        if (is_bool($value)) { // already a boolean
            return $value;
        }
        if (is_int($value)) { // strict integer check 1 is true, all others are false
            return $value === 1;
        }
        if (is_float($value)) {
            return null; // reject floats explicitly
        }

        // match string or numeric-like string values
        return match (strtolower(trim((string)$value))) {
            "yes", "1", "true" => true,     // truthy values
            "no", "0", "false" => false,    // falsy values
            default => null                 // invalid input
        };
    }

    protected function toInteger(mixed $value): ?int {
        if (is_int($value)) {
            // It's strictly an integer (e.g., 42)
            return $value;
        }

        if (is_float($value)) {
            // Floats cannot be integers unless they're whole numbers
            return floor($value) == $value ? (int)$value : null;
        }

        if (is_string($value)) {
            // Check if the string represents a valid bigint
            if (preg_match('/^-?\d+$/', trim($value))) {
                // Convert it to an integer if within PHP's integer range
                $intValue = (int)$value;
                if ((string)$intValue === trim($value)) {
                    return $intValue;
                }
                // The value is out of PHP's `int` range (bigint-like)
                return null; // Or handle as needed
            }
        }

        // Reject everything else
        return null;
    }

    protected function toUnsignedInteger(mixed $value): ?int {
        if (is_int($value)) {
            // Ensure the value is positive (unsigned)
            return $value >= 0 ? $value : null;
        }

        if (is_float($value)) {
            // Float-safe conversion only if it's a whole number and positive
            return ($value >= 0 && floor($value) == $value) ? (int)$value : null;
        }

        if (is_string($value)) {
            // Check if it's a valid unsigned integer string
            if (preg_match('/^\d+$/', trim($value))) {
                $intValue = (int)$value;
                // Ensure the cast value matches the original to avoid overflow
                return (string)$intValue === trim($value) ? $intValue : null;
            }
        }

        // Reject all other types
        return null;
    }

    protected function toDateTime(mixed $value, string $format = "Y-m-d H:i:s"): ?DateTime
    {
        if ($value instanceof DateTime) {
            // already a DateTime object
            return $value;
        }

        if (is_string($value)) {
            // normalize and validate string input
            $value = trim($value);
            // try creating DateTime from a specific format
            $dateTime = DateTime::createFromFormat($format, $value);
            if ($dateTime !== false && $dateTime->format($format) === $value) {
                return $dateTime; // valid datetime with expected format
            }

            // fallback: try parsing using strtotime (e.g., "2023-10-21 14:30")
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return (new DateTime())->setTimestamp($timestamp);
            }
        }

        // for everything else (null, array, bool, etc.), reject as invalid
        return null;
    }

    protected function toDate(mixed $value, string $format = "Y-m-d"): ?DateTime
    {
        if ($value instanceof DateTime) {
            // normalize time - date-only fields should always be midnight
            return (clone $value)->setTime(0, 0, 0);
        }
        if (is_string($value)) {
            // normalize and validate string input
            $value = trim($value);
            // try creating DateTime from a specific format
            $dateTime = DateTime::createFromFormat($format, $value);
            $errors = DateTime::getLastErrors();

            // reject if parsing failed OR if PHP had to "guess"
            if ($dateTime !== false && empty($errors['warning_count']) && empty($errors['error_count'])) {
                // normalize to midnight
                return $dateTime->setTime(0, 0, 0);
            }
        }
        // if nothing else is caught, this will fire
        return null;
    }

    protected function toDateTimeImmutable(mixed $value, string $format = "Y-m-d H:i:s"): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            // value is already a DateTimeImmutable
            return $value;
        }

        if ($value instanceof \DateTime) {
            // convert DateTime to DateTimeImmutable
            return DateTimeImmutable::createFromMutable($value);
        }

        if (is_string($value)) {
            // normalize and validate string input
            $value = trim($value);

            // try creating DateTimeImmutable from a specific format
            $dateTimeImmutable = DateTimeImmutable::createFromFormat($format, $value);
            if ($dateTimeImmutable !== false && $dateTimeImmutable->format($format) === $value) {
                return $dateTimeImmutable;
            }

            // fallback: try parsing the string using strtotime
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return (new DateTimeImmutable())->setTimestamp($timestamp);
            }
        }

        // reject unsupported types
        return null;
    }

    public function validatePassword(string $password): bool|string
    {
        if (str_starts_with($password, '$2y$')) {
            return $password;
        }
        $pattern = "^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$";
        if (preg_match("/$pattern/", $password) === 1) {
            return $password;
        }
        return false;
    }
}

