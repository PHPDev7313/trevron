<?php

namespace Tests\Stubs\Bootstrap;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class NullBootstrapPhase implements BootstrapPhaseInterface
{
    public function __construct(
        private readonly BoostrapPhase $phase
    ) {}

    public function phase(): BoostrapPhase
    {
        return $this->phase;
    }

    public function bootstrap(Container $container): void
    {
        // intentionally no-op
    }
}

