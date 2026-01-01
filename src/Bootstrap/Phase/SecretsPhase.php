<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 29, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *
 * SECURITY CRITICAL PHASE
 *
 * This phase locks all secrets and MUST:
 * - Run exactly once
 * - Run after secrets service registration
 * - Never be bypassed or reordered
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

