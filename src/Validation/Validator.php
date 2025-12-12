<?php

namespace JDS\Validation;

use JDS\Contracts\Validation\Attribute\ValidationAttributeInterface;
use JDS\Exceptions\Validation\ValidationException;
use ReflectionAttribute;
use ReflectionParameter;

class Validator
{
    /**
     * @throws ValidationException
     */
    public function validateParameter(ReflectionParameter $param, mixed $value): void
    {
        $attributes = $param->getAttributes(ValidationAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attr) {
            $rule = $attr->newInstance();
            $rule->validate($value, $param->getName());
        }
    }
}

