<?php

namespace JDS\Contracts\Validation\Attribute;



use JDS\Exceptions\Validation\ValidationException;

interface ValidationAttributeInterface
{
    /**
     * @param mixed  $value The parameter value to validate
     * @param string $paramName Parameter name for error reporting
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $paramName): void;
}

