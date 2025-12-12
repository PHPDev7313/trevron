<?php

namespace JDS\Contracts\Http;

use JDS\Exceptions\Validation\ValidationException;

interface ParamTransformerInterface
{
    /**
     * @param mixed  $value The raw value from route params
     * @param string $targetType Fully-qualified class name
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    public function transform(mixed $value, string $targetType): mixed;

    /**
     * Whether this transformer supports converting to the given type.
     */
    public function supports(string $targetType): bool;
}

