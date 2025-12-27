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

namespace JDS\Bootstrap;

use FastRoute\Dispatcher;
use JDS\Contracts\Routing\LockableRouterInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Routing\ProcessedRoutes;
use JDS\Routing\RouteBootstrap;

final class RoutingBootstrap implements LockableRouterInterface
{
    private bool $locked = false;

    public function __construct(
        private readonly ProcessedRoutes $routes,
        private Dispatcher $dispatcher,
    ) {}

    public function bootstrap(): Dispatcher
    {
        if ($this->locked) {
            throw new BootstrapInvariantViolationException(
                "Routing already bootstrapped and locked."
            );
        }

        $this->dispatcher = RouteBootstrap::buildDispatcher($this->routes);

        return $this->dispatcher;
    }

    public function lock(): void
    {
        if ($this->dispatcher === null) {
            throw new BootstrapInvariantViolationException(
                "Routing cannot be locked before dispatcher exists."
            );
        }

        $this->locked = true;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function dispatcher(): Dispatcher
    {
        if (!$this->locked) {
            throw new BootstrapInvariantViolationException(
                "Dispatcher accessed before routing was locked."
            );
        }

        return $this->dispatcher;
    }
}

