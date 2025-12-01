<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class DateRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $format = $params[0] ?? 'Y-m-d';
        $dt = \DateTimeImmutable::createFromFormat($format, (string)$value);

        if (!$dt || $dt->format($format) !== (string)$value) {
            return "{$field} must be a valid date in format {$format}.";
        }

        return null;
    }
}