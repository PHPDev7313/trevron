<?php

namespace JDS\Validation\Attribute;

use JDS\Contracts\Validation\Attribute\ValidationAttributeInterface;
use JDS\Exceptions\Validation\ValidationException;

class Regex implements ValidationAttributeInterface
{
    public function __construct(
        private readonly string $pattern
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, string $paramName): void
    {
        if (!is_string($value)) {
            throw new ValidationException("Parameter '{$paramName}' must be string to apply Regex.");
        }

        if (!preg_match($this->pattern, $value)) {
            throw new ValidationException("Parameter '{$paramName}' does not match required pattern.");
        }
    }
}

