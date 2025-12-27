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

namespace JDS\Contracts\Bootstrap;

enum BoostrapPhase: int
{
    case CONFIG = 10; // configuration is loaded
    case ROUTING = 20; // routes + dispatcher wired
    case SECRETS = 30; // secrets validated + locked
    case COMMANDS = 40; // console commands registered
}

