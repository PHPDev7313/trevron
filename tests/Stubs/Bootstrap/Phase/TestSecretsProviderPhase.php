<?php

namespace Tests\Stubs\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Security\LockableSecretsInterface;
use League\Container\Container;

class TestSecretsProviderPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::CONFIG;
    }

    public function bootstrap(Container $container): void
    {
        $container->add(
            LockableSecretsInterface::class,
            new TestLockableSecrets()
        );
    }
}

