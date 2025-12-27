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

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapAwareContainerInterface;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Exceptions\Bootstrap\BootstrapMissingPhaseException;
use League\Container\Container;

final class BootstrapRunner
{
    /** @var BootstrapPhaseInterface[] */
    private array $phases = [];

    /** @param list<BoostrapPhase> */
    private array $registeredPhases = [];

    private const REQUIRED_PHASES = [
        BoostrapPhase::CONFIG,
        BoostrapPhase::ROUTING,
        BoostrapPhase::SECRETS,
        BoostrapPhase::COMMANDS,
    ];

    public function __construct(
        private readonly Container $container,
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
        $this->assertRequiredPhasesPresent();
        $this->assertPhaseOrder();

        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->enterBootstrap();
        }

        foreach ($this->phases as $k => $phase) {
            $phase->bootstrap($this->container);
        }

        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->exitBootstrap();
        }

        $this->lockCommandRegistry();
    }

    private function assertRequiredPhasesPresent(): void
    {
        foreach (self::REQUIRED_PHASES as $required) {
            if (!in_array($required, $this->registeredPhases, true)) {
                throw new BootstrapInvariantViolationException(
                    "Required bootstrap phase missing: {$required->name}"
                );
            }
        }
    }

    private function assertPhaseOrder(): void
    {
        $values = array_map(fn(BoostrapPhase $p) => $p->value, $this->registeredPhases);
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


