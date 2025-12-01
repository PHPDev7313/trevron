<?php

declare(strict_types=1);

namespace JDS\Contracts\Validation;

use JDS\Validation\ValidationResult;

interface ValidatorInterface
{
    /**
     * @param array $data Input data (field => value)
     * @param array $rules Rules definition (field => string[] of rule definitions)
     *
     * @return ValidationResult
     */
    public function validate(array $data, array $rules): ValidationResult;
}

