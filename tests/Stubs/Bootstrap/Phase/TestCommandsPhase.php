<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class TestCommandsPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::COMMANDS;
    }

    public function bootstrap(Container $container): void
    {
        // no-op
    }
}

