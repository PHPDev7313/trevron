<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class EmailRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '') {
            return null; // use 'required' for non-empty
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "{$field} must be a valid email address.";
        }

        return null;
    }
}

