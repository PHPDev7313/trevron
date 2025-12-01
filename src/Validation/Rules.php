<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class Rules implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return "{$field} is required";
        }

        return null;
    }
}

