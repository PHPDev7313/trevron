<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class ArrayRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null) {
            return null; // use 'required' for non-null
        }

        if (!is_array($value)) {
            return "{$field} must be an array.";
        }

        return null;
    }
}

