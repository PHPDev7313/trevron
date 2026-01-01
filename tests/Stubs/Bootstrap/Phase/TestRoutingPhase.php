<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class TestRoutingPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::ROUTING;
    }

    public function bootstrap(Container $container): void
    {
        // no-op
    }
}

