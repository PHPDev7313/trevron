<?php

namespace Tests\Stubs\Bootstrap;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

class StubPhase implements BootstrapPhaseInterface
{
    public function __construct(private BoostrapPhase $phase) {}

    public function phase(): BoostrapPhase
    {
        return $this->phase;
    }

    public function bootstrap(Container $container): void
    {
        // no-op by design
    }
}