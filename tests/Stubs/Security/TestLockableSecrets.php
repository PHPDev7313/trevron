<?php

namespace Tests\Stubs\Security;

use JDS\Contracts\Security\LockableSecretsInterface;

class TestLockableSecrets implements LockableSecretsInterface
{
    private bool $locked = false;

    public function __construct(private array $secrets)
    {
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @inheritDoc
     */
    public function get(string $path, mixed $default = null): mixed
    {
        return $this->secrets[$path] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->secrets;
    }

    public function has(string $path): bool
    {
        return array_key_exists($path, $this->secrets);
    }
}