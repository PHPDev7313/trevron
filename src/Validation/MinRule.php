<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;

class MinRule implements ValidationRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(string $field, mixed $value, array $params = []): ?string
    {
        $min = isset($params[0]) ? (int)$params[0] : 0;
        $length = strlen((string)$value);

        if ($length < $min) {
            return "{$field} must be at least {$min} characters.";
        }
        return null;
    }
}