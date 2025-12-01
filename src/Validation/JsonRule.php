<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class JsonRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '') {
            return null; // use 'required' if you want non-empty
        }

        json_decode((string)$value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "{$field} must be a valid JSON string.";
        }

        return null;
    }
}