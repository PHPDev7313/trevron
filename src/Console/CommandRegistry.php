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
use League\Container\Container;

final class CommandRegistry implements CommandRegistryInterface
{
    public function __construct(
        private readonly Container $container
    )
    {
    }

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

    public function dispatchFromArgv(array $argv): int
    {
        // argv[0] = script name
        // argv[1] = command name
        $commandName = $argv[1] ?? null;

        if (!is_string($commandName) || $commandName === '') {
            throw new ConsoleRuntimeException(
                "No command specified. [Command:Registry]."
            );
        }

        // Ensure command was registerd
        if (!in_array($this->resolveCommandClass($commandName), $this->commands, true)) {
            throw new ConsoleRuntimeException(
                "Unknown command '{$commandName}'. [Command:Registry]."
            );
        }

        // Resolve command from container by name

        if (!$this->container->has($commandName)) {
            throw new ConsoleRuntimeException(
                "Command '{$commandName}' not bound in container. [Command:Registry]."
            );
        }

        /** @var CommandInterface $command */
        $command = $this->container->get($commandName);

        // Parse CLI options: --flag
        $params = [];
        foreach (array_slice($argv, 2) as $arg) {
            if (str_starts_with($arg, '--')) {
                $params[ltrim($arg, '-')] = true;
            }
        }

        return $command->execute($params);
    }

    private function resolveCommandClass(string $commandName): string
    {
        foreach ($this->commands as $class) {
            if (!property_exists($class, 'name')) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            $property = $reflection->getProperty('name');

            if ($property->isDefault() && $property->getDefaultValue() === $commandName) {
                return $class;
            }
        }

        return '';
    }
}

