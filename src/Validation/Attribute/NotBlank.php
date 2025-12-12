<?php

namespace JDS\Validation\Attribute;

use JDS\Contracts\Validation\Attribute\ValidationAttributeInterface;
use JDS\Exceptions\Validation\ValidationException;

class NotBlank implements ValidationAttributeInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, string $paramName): void
    {
        if ($value === null || $value === "" || (is_string($value) && trim($value)) === "") {
            throw new ValidationException("Parameter '{$paramName}' cannot be blank.");
        }
    }
}

