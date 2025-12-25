<?php

declare(strict_types=1);

namespace JDS\Bootstrap;

use JDS\Contracts\Bootstrap\BootstrapAwareContainerInterface;
use JDS\Exceptions\Bootstrap\BootstrapResolutionNotAllowedException;
use League\Container\Container;

final class BootstrapContainer extends Container implements BootstrapAwareContainerInterface
{
    private bool $bootstrapping = false;

    public function enterBootstrap(): void
    {
        $this->bootstrapping = true;
    }

    public function exitBootstrap(): void
    {
        $this->bootstrapping = false;
    }

    /**
     * Guard service resolution during bootstrap
     */
    public function get($id)
    {
        if ($this->bootstrapping) {
            throw new BootstrapResolutionNotAllowedException(
                "Service resolution is forbidden during bootstrap. Tried to resolve: {$id}"
            );
        }

        return parent::get($id);
    }

    public function getNew($id, array $args = [])
    {
        if ($this->bootstrapping) {
            throw new BootstrapResolutionNotAllowedException(
                "Service resolution is forbidden during bootstrap (getNew). Tried to resolve: {$id}"
            );
        }

        return parent::getNew($id, $args);
    }
}


