<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Security\LockableSecretsInterface;
use LogicException;

class TestLockableSecrets implements LockableSecretsInterface
{
    private bool $locked = false;
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
        throw new LogicException(
            "TestLockableSecrets::get() should never be called in SecretsPhase tests."
        );
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        throw new LogicException(
            "TestLockableSecrets::all() should never be called in SecretsPhase tests."
        );
    }

    public function has(string $path): bool
    {
        throw new LogicException(
            "TestLockableSecrets::has() should never be called in SecretsPhase tests."
        );
    }
}


