<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 23, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapARCHITECTURE.v1.2.FINAL.md
 */

declare(strict_types=1);

namespace JDS\Bootstrap;

use JDS\Contracts\Bootstrap\BootstrapAwareContainerInterface;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

final class BootstrapRunner
{
    /** @var BootstrapPhaseInterface[] */
    private array $phase = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function addPhase(BootstrapPhaseInterface $phase): void
    {
        $this->phase[] = $phase;
    }

    public function run(): void
    {
        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->enterBootstrap();
        }

        foreach ($this->phase as $phase) {
            $phase->bootstrap($this->container);
        }

        if ($this->container instanceof BootstrapAwareContainerInterface) {
            $this->container->exitBootstrap();
        }
    }
}


