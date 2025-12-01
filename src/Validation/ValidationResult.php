<?php

declare(strict_types=1);

namespace JDS\Validation;

class ValidationResult
{
    public function __construct(
        private array $errors = []
    )
    {
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array<string, string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}

