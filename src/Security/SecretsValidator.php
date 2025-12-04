<?php

namespace JDS\Security;

use JDS\Exceptions\CryptoRuntimeException;

class SecretsValidator
{
    /**
     * @param array<string, mixed> $schema
     */
    public function __construct(
        private readonly array $schema
    )
    {
    }

    /**
     * @param array<string, mixed> $secrets
     *
     * @throws CryptoRuntimeException
     */
    public function validate(array $secrets): void
    {
        $this->validateRecursive($this->schema, $secrets, '');
    }

    private function validateRecursive(array $schema, array $data, string $path): void
    {
        foreach ($schema as $key => $schemaValue) {
            $currentPath = $path === '' ? $key : "{$path}.{$key}";

            if (!array_key_exists($key, $data)) {
                throw new CryptoRuntimeException("Missing required secret: {$currentPath}");
            }

            if (is_array($schemaValue)) {
                if (!is_array($data[$key])) {
                    throw new CryptoRuntimeException("Invalid structure at {$currentPath}: expected object");
                }

                $this->validateRecursive($schemaValue, $data[$key], $currentPath);
            }
        }
    }
}

