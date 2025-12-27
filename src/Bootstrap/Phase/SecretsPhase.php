<?php

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

class SecretsPhase implements BootstrapPhaseInterface
{

    public function phase(): BoostrapPhase
    {
        return BoostrapPhase::SECRETS;
    }

    public function bootstrap(Container $container): void
    {
        if (!$container->has(SecretsInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Secrets service not registered before SECRETS phase."
            );
        }

        $secrets = $container->get(SecretsInterface::class);

        if (!$secrets instanceof LockableSecretsInterface) {
            throw new BootstrapInvariantViolationException(
                "Secrets implementation is not lockable."
            );
        }

        $secrets->lock();
    }
}

