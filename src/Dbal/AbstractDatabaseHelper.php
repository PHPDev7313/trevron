<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;

abstract class AbstractDatabaseHelper
{
    /**
     * @throws Exception
     */
    public function bind(Statement $statement, string $parameter, mixed $value, ?int $type = null): void
    {
        // Automatically determine type if not specified
        if ($type === null) {
            if (is_int($value)) {
                $type = ParameterType::INTEGER;
            } elseif (is_float($value)) {
                $value = (string)$value;
                $type = ParameterType::STRING;
            } elseif (is_bool($value)) {
                $type = ParameterType::BOOLEAN;
            } elseif (is_null($value)) {
                $type = ParameterType::NULL;
            } else {
                $type = ParameterType::STRING;
            }
        }
        $statement->bindValue($parameter, $value, $type);
    }
}

