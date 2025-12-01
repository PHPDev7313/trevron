<?php

declare(strict_types=1);

namespace JDS\Contracts\Validation;

interface ValidationRuleInterface
{
    /**
     * @param string $field The field name (e.g. 'email')
     * @param mixed  $value The value to validate
     * @param array  $params Extra parameters for the rule (e.g. ['3'] for min:3)
     *
     * @return string|null Error message if invalid, null if valid
     */
    public function validate(string $field, mixed $value, array $params = []): ?string;
}

