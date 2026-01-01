<?php

namespace Tests\Stubs\Bootstrap;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class NullBootstrapPhase implements BootstrapPhaseInterface
{
    public function __construct(
        private readonly BootstrapPhase $phase
    ) {}

    public function phase(): BootstrapPhase
    {
        return $this->phase;
    }

    public function bootstrap(Container $container): void
    {
        // intentionally no-op
    }
}

