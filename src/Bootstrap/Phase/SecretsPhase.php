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
        if (!$container->has(LockableSecretsInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Lockable secrets service must be registered before SECRETS phase."
            );
        }

        /** @var LockableSecretsInterface $secrets */
        $secrets = $container->get(LockableSecretsInterface::class);

        if ($secrets->isLocked()) {
            throw new BootstrapInvariantViolationException(
                "SECRETS phase executed more than once."
            );
        }

        $secrets->lock();
    }
}

