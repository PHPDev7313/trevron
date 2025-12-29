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

namespace JDS\Console;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Exceptions\Console\ConsoleRuntimeException;

final class CommandRegistry implements CommandRegistryInterface
{
    /** @var class-string<CommandInterface>[] */
    private array $commands = [];

    private bool $locked = false;

    public function register(string $commandClass): void
    {
        if ($this->locked) {
            throw new BootstrapInvariantViolationException(
                "Commands may not be registered after the COMMAND phase. [Console:Registry]."
            );
        }

        if (!class_exists($commandClass)) {
            throw new ConsoleRuntimeException(
                "Command class does not exist: {$commandClass}. [Console:Registery]."
            );
        }

        if (!is_subclass_of($commandClass, CommandInterface::class)) {
            throw new ConsoleRuntimeException(
                "Registered command must implement CommandInterface: {$commandClass}. [Console:Registery]."
            );
        }

        if (!in_array($commandClass, $this->commands, true)) {
            $this->commands[] = $commandClass;
        }
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->commands;
    }

}

