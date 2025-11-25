<?php

namespace JDS\Contracts\Dbal;

interface JSONValidatorInterface
{
    public static function validate(string $jsonString): array;

}

