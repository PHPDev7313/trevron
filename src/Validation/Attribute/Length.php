<?php

namespace JDS\Validation\Attribute;

use JDS\Contracts\Validation\Attribute\ValidationAttributeInterface;
use JDS\Exceptions\Validation\ValidationException;

class Length implements ValidationAttributeInterface
{
    public function __construct(
        private readonly ?int $min = null,
        private readonly ?int $max = null
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, string $paramName): void
    {
        if (!is_string($value)) {
            throw new ValidationException("Parameter '{$paramName}' must be string for length validation.");
        }

        $len = mb_strlen($value);

        if ($this->min !== null && $len < $this->min) {
            throw new ValidationException("Parameter '{$paramName}' must be at least {$this->min} characters.");
        }

        if ($this->max !== null && $len > $this->max) {
            throw new ValidationException("Parameter '{$paramName}' must be at most {$this->max} characters.");
        }
    }
}

