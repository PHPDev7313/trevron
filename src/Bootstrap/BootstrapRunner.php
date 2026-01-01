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

declare(strict_types=1);

namespace JDS\Bootstrap;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapAwareContainerInterface;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

final class BootstrapRunner
{
    /** @var BootstrapPhaseInterface[] */
    private array $phases = [];

    public function __construct(
        private readonly Container $container,
        private array $registeredPhases
    ) {}

    public function addPhase(BootstrapPhaseInterface $phase): void
    {
        $phaseEnum = $phase->phase();

        // ❌ No duplicate phases
        if (in_array($phaseEnum, $this->registeredPhases, true)) {
            throw new BootstrapInvariantViolationException(
                "Duplicate bootstrap phase registerd: {$phaseEnum->name}"
            );
        }

        $this->registeredPhases[] = $phaseEnum;
        $this->phases[] = $phase;
    }

    public function run(): void
    {
        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->enterBootstrap();
        }

        $this->assertRequiredPhasesPresent();
        $this->assertPhaseOrder();

        foreach ($this->phases as $phase) {
            // 🔓 Allow resolution ONLY during the phase
            if ($this->container instanceof BootstrapContainer) {
                $this->container->allowResolution();
            }

            $phase->bootstrap($this->container);

            // 🔒 Lock resolution again
            if ($this->container instanceof BootstrapContainer) {
                $this->container->forbidResolution();
            }
        }

        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->exitBootstrap();
        }

        $this->lockCommandRegistry();
    }

    private function assertRequiredPhasesPresent(): void
    {
        foreach ($this->registeredPhases as $required) {
            if (!in_array($required, $this->registeredPhases, true)) {
                throw new BootstrapInvariantViolationException(
                    "Required bootstrap phase missing: {$required->name}"
                );
            }
        }
    }

    private function assertPhaseOrder(): void
    {
        $values = array_map(fn(BootstrapPhase $p) => $p->value, $this->registeredPhases);
        $sorted = $values;
        sort($sorted);

        if ($values !== $sorted) {
            throw new BootstrapInvariantViolationException(
                "Bootstrap phases are registered out of order."
            );
        }
    }

    private function lockCommandRegistry(): void
    {
        if ($this->container->has(CommandRegistryInterface::class)) {
            $registry = $this->container->get(CommandRegistryInterface::class);

            if (method_exists($registry, 'lock')) {
                $registry->lock();
            }
        }
    }
}


