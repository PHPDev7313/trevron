<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class TestRoutingPhase implements BootstrapPhaseInterface
{

    public function phase(): BoostrapPhase
    {
        return BoostrapPhase::ROUTING;
    }

    public function bootstrap(Container $container): void
    {
        // no-op
    }
}

