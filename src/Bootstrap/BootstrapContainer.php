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

use JDS\Contracts\Bootstrap\BootstrapAwareContainerInterface;
use JDS\Exceptions\Bootstrap\BootstrapResolutionNotAllowedException;
use League\Container\Container;

final class BootstrapContainer extends Container implements BootstrapAwareContainerInterface
{
    private bool $bootstrapping = false;
    private bool $resolutionAllowed = false;

    public function enterBootstrap(): void
    {
        $this->bootstrapping = true;
    }

    public function exitBootstrap(): void
    {
        $this->bootstrapping = false;
    }

    public function allowResolution(): void
    {
        $this->resolutionAllowed = true;
    }

    public function forbidResolution(): void
    {
        $this->resolutionAllowed = false;
    }

    /**
     * Guard service resolution during bootstrap
     */
    public function get($id)
    {
        if ($this->bootstrapping && !$this->resolutionAllowed) {
            throw new BootstrapResolutionNotAllowedException(
                "Service resolution is forbidden during bootstrap. Tried to resolve: {$id}"
            );
        }

        return parent::get($id);
    }

    public function getNew($id, array $args = [])
    {
        if ($this->bootstrapping && !$this->resolutionAllowed) {
            throw new BootstrapResolutionNotAllowedException(
                "Service resolution is forbidden during bootstrap (getNew). Tried to resolve: {$id}"
            );
        }

        return parent::getNew($id, $args);
    }
}


