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
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
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

    /** @var list<BootstrapPhase> */
    private array $registeredPhases = [];

    /** @var list<BootstrapPhase> */
    private array $requiredPhases;

    /** @var list<BootstrapPhase> */
    private array $addedPhases = [];

    public function __construct(
        private readonly Container $container,
        array $requiredPhases
    ) {
        $this->requiredPhases = $requiredPhases;
    }

    public function addPhase(BootstrapPhaseInterface $phase): void
    {
        $phaseEnum = $phase->phase();

        // ❌ No duplicate phases
        if (in_array($phaseEnum, $this->registeredPhases, true)) {
            if (!$phaseEnum->isRepeatable()) {
                throw new BootstrapInvariantViolationException(
                    "Bootstrap phase {$phaseEnum->name} is non-repeatable and was registered more than once. [Bootstrap:Runner]."
                );
            }
        }

        $this->addedPhases[] = $phaseEnum;
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
        foreach ($this->requiredPhases as $required) {
            if (!in_array($required, $this->addedPhases, true)) {
                throw new BootstrapInvariantViolationException(
                    "Required bootstrap phase missing: {$required->name}. [Bootstrap:Runner]."
                );
            }
        }
    }

    private function assertPhaseOrder(): void
    {
        $values = array_map(
            fn (BootstrapPhase $p) => $p->value,
            $this->addedPhases
        );

        $sorted = $values;
        sort($sorted);

        if ($values !== $sorted) {
            throw new BootstrapInvariantViolationException(
                'Bootstrap phases are registered out of order. [Bootstrap:Runner].'
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


