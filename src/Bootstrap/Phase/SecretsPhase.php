<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 3, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.FINAL.md
 */

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

final class SecretsPhase implements BootstrapPhaseInterface
{
    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::SECRETS;
    }

    public function bootstrap(Container $container): void
    {
        if (!$container->has(LockableSecretsInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Lockable secrets service must be registered before SECRETS phase."
            );
        }

        /** @var LockableSecretsInterface $secrets */
        $secrets = $container->get(LockableSecretsInterface::class);

        if ($secrets->isLocked()) {
            throw new BootstrapInvariantViolationException(
                "SECRETS phase executed more than once."
            );
        }

        $secrets->lock();
    }
}

