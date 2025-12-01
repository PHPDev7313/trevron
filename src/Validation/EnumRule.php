<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class EnumRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '') {
            return null; // optional unless 'required'
        }

        if (!in_array($value, $params, true)) {
            $allowed = implode(', ', $params);
            return "{$field} must be one of: {$allowed}.";
        }

        return null;
    }
}

