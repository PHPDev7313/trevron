<?php

namespace Tests\Contract\Stubs\Secrets;

use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;

class FakeLockableSecrets implements SecretsInterface, LockableSecretsInterface
{
    private bool $locked = false;

    /**
     * @inheritDoc
     */
    public function get(string $path, mixed $default = null): mixed
    {
        return 'value';
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return ['x' => 'y'];
    }

    public function has(string $path): bool
    {
        return true;
    }

    public function lock(): void
    {
        if ($this->locked) {
            throw new BootstrapInvariantViolationException(
                "secrets already locked."
            );
        }

        $this->locked = true;
    }

    public function islocked(): bool
    {
        return $this->locked;
    }
}

