<?php

namespace JDS\Validation;

use JDS\Contracts\Validation\Attribute\ValidationAttributeInterface;
use JDS\Exceptions\Validation\ValidationException;
use ReflectionAttribute;
use ReflectionParameter;

final class MethodParameterValidator
{
    /**
     * Validate a method argument using its attributes.
     *
     * @throws ValidationException
     */
    public function validateParameter(ReflectionParameter $parameter, mixed $value): void
    {
        $attributes = $parameter->getAttributes(
            ValidationAttributeInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attr) {

            /** @var ValidationAttributeInterface $instance */
            $instance = $attr->newInstance();

            //
            // Each attribute defines its own validation rules.
            //
            $instance->validate(
                value: $value,
                paramName: $parameter->getName()
            );
        }
    }

    /**
     * Validate all parameters for a method call.
     * (Optional helper for future use)
     *
     * @param ReflectionParameter[] $parameters
     * @param array<string,mixed> $providedArgs
     *
     * @throws ValidationException
     */
    public function validateParameters(array $parameters, array $providedArgs): void
    {
        foreach ($parameters as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $providedArgs)) {
                $this->validateParameter($param, $providedArgs[$name]);
            }
        }
    }
}
