<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 27, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 */

declare(strict_types=1);

namespace JDS\Bootstrap\Phase;

use JDS\Console\CommandRegistry;
use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

final class CommandsPhase implements BootstrapPhaseInterface
{
    /** @param list<class-string> $commands */
    public function __construct(
        private readonly array $commands
    ) {}

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::COMMANDS;
    }

    public function bootstrap(Container $container): void
    {
        if ($container->has(CommandRegistryInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "CommandRegistry registerd more than once."
            );
        }

        // Register fully populated, immutable registry
        $commands = $this->commands;


        // Register populated registry lazily (NO get() here)
        $container->addShared(CommandRegistryInterface::class, function () use ($commands) {
            $registry = new CommandRegistry();

            foreach ($commands as $commandClass) {
                $registry->register($commandClass);
            }

            $registry->lock();

            return $registry;
        });

        // Optional: alias concrete type too
        $container->addShared(
            CommandRegistry::class,
            function () use ($container) {
                return $container->get(CommandRegistryInterface::class);
            }
            // this means the same thing as the function () use ...
//            fn () => $container->get(CommandRegistryInterface::class)
        );
    }
}

