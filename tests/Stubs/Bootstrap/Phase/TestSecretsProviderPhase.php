<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Security\LockableSecretsInterface;
use League\Container\Container;

class TestSecretsProviderPhase implements BootstrapPhaseInterface
{

    public function phase(): BoostrapPhase
    {
        return BoostrapPhase::CONFIG;
    }

    public function bootstrap(Container $container): void
    {
        $container->add(
            LockableSecretsInterface::class,
            new TestLockableSecrets()
        );
    }
}

