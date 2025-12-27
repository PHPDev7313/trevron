<?php

declare(strict_types=1);

namespace JDS\Bootstrap\Invariant;

use JDS\Contracts\Bootstrap\BootstrapInvariantInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Security\Secrets;
use League\Container\Container;
use Throwable;

final class SecretsInvariant implements BootstrapInvariantInterface
{
    public function __construct(
        private readonly bool $required // allow client to decide env behavior
    )
    {
    }

    public function assert(Container $container): void
    {
        if (!$this->required) {
            return;
        }

        if (!$container->has(SecretsInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Secrets service missing: " . SecretsInterface::class
            );
        }

        // Also ensure it actually resolves (guards broken schema/key/file)
        try {
            $container->get(SecretsInterface::class);
        } catch (Throwable $e) {
            throw new BootstrapInvariantViolationException(
                "Secrets failed to resolve during finalize: " . $e->getMessage()
            );
        }
    }
}

