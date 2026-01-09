<?php
/**
 * RoutingPhase (v1.2 FINAL)
 *
 * This phase exists for bootstrap ordering only.
 *
 * All routing compilation (ProcessRoutes) and dispatcher construction
 * occur prior to bootstrap. No runtime mutation, locking, or validation
 * is permitted here.
 *
 * Enforcement is performed via contract tests, not runtime guards.
 */
namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class RoutingPhase implements BootstrapPhaseInterface
{
    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::ROUTING;
    }

    public function bootstrap(Container $container): void
    {
        // v1.2 FINAL
        // Routing is compiled and immutable.
        // This phase exists for ordering only.
    }
}

