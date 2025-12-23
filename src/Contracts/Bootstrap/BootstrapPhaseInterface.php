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

namespace JDS\Contracts\Bootstrap;

use League\Container\Container;

interface BootstrapPhaseInterface
{
    public function bootstrap(Container $container): void;
}


