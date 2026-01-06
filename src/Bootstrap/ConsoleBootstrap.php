<?php

namespace JDS\Bootstrap;

use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EditSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;
use JDS\Console\CommandRegistry;
use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

final class ConsoleBootstrap implements BootstrapPhaseInterface
{

    public function bootstrap(Container $container): void
    {
        $container->add(CommandRegistry::class, CommandRegistry::class)
            ->setShared(true);

        // register command classes ONLY
        $container->add(EncryptSecretsCommand::class, EncryptSecretsCommand::class);
        $container->add(DecryptSecretsCommand::class, DecryptSecretsCommand::class);
        $container->add(EditSecretsCommand::class, EditSecretsCommand::class);
    }

    public function phase(): BootstrapPhase
    {
        // TODO: Implement phase() method.
    }
}

