<?php

namespace JDS\Dbal;

interface JSONValidatorInterface
{
    public static function validate(string $jsonString): array;

}

