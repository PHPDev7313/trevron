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

namespace JDS\Bootstrap;

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
        foreach ($this->phase as $phase) {
            if (!$phase instanceof BootstrapPhaseInterface) {
                throw new \RuntimeException(
                    'All bootstrap phses must implement BootstrapPhaseInterface.'
                );
            }

            $phase->bootstrap($this->container);
        }
    }
}


