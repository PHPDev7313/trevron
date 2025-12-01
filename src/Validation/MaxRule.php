<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class MaxRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        if ($value === null) {
            return null;
        }

        $max = isset($params[0]) ? (int)$params[0] : PHP_INT_MAX;
        $length = strlen((string)$value);

        if ($length > $max) {
            return "{$field} may not be greater thatn {$max} characters.";
        }

        return null;
    }
}

