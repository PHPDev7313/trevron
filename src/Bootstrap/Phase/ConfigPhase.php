<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class ConfigPhase implements BootstrapPhaseInterface
{

    public function phase(): BoostrapPhase
    {
        return BoostrapPhase::CONFIG;
    }

    public function bootstrap(Container $container): void
    {
        // Config already loaded before container creation.
        // Phase exists purely for ordering + invariants.
    }
}

