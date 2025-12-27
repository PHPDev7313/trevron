<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 27, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 */

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

