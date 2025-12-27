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

use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;

final class Secrets implements SecretsInterface, LockableSecretsInterface
{
    private bool $locked = false;

    /** @param array<string, mixed> $secrets */
    private array $secrets;

    public function __construct(array $secrets)
    {
        $this->secrets = self::deepCopy($secrets);
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return $this->resolve($path, $default);
    }

    public function has(string $path): bool
    {
        return $this->resolve($path, '__missing__') !== '__missing__';
    }

    public function all(): array
    {
        if (!$this->locked) {
            throw new BootstrapInvariantViolationException(
                'Secrets accessed before being locked.'
            );
        }

        return self::deepCopy($this->secrets);
    }

    private function resolve(string $path, mixed $default): mixed
    {
        $segments = explode('.', $path);
        $value = $this->secrets;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private static function deepCopy(array $array): array
    {
        return unserialize(serialize($array));
    }
}
