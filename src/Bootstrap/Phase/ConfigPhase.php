<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class ConfigPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::CONFIG;
    }

    public function bootstrap(Container $container): void
    {
        // Config already loaded before container creation.
        // Phase exists purely for ordering + invariants.
    }
}

