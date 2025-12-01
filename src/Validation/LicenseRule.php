<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class LicenseRule implements ValidationRuleInterface
{

    private const PATTERN = "/^[A-Za-z0-9]{6}-[A-Za-z0-9]{6}-[A-Za-z0-9]{6}-[A-Za-z0-9]{6}$/";
    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '') {
            return null; // optional by default, pair with 'required' if needed
        }

        if (!preg_match(self::PATTERN, (string)$value)) {
            return "{$field} is not a valid license.";
        }

        return null;
    }
}