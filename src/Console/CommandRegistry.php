<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 3, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

declare(strict_types=1);

namespace JDS\Console;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Exceptions\Console\ConsoleRuntimeException;
use League\Container\Container;
use ReflectionClass;

final class CommandRegistry implements CommandRegistryInterface
{
    public function __construct(
        private readonly Container $container
    ) {}

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
                "Command class does not exist: {$commandClass}. [Console:Registry]."
            );
        }

        if (!is_subclass_of($commandClass, CommandInterface::class)) {
            throw new ConsoleRuntimeException(
                "Registered command must implement CommandInterface: {$commandClass}. [Console:Registry]."
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

    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Dispatch a command directly from argv.
     * Supported:
     *   php bin/console <command:name> [--flag] [--flag2]
     */
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

        // Resolve command class (proves it's registered)
        $commandClass = $this->resolveCommandClass($commandName);

        // Ensure container binding exists for the command name alias
        // (Kernel usually creates these aliases; tooling mode must also create them or bind directly)
        if (!$this->container->has($commandName)) {
            // Helpful error: show the class we expected
            throw new ConsoleRuntimeException(
                "Command '{$commandName}' ({$commandClass}) not bound in container. [Command:Registry]."
            );
        }

        /** @var CommandInterface $command */
        $command = $this->container->get($commandName);

        // Parse CLI options: --flag only
        $params = [];
        foreach (array_slice($argv, 2) as $arg) {
            if (!is_string($arg) || $arg === '') {
                continue;
            }

            // must start with --
            if (!str_starts_with($arg, '--')) {
                throw new ConsoleRuntimeException(
                    "Invalid argument '{$arg}'. Only '--flag' options are supported. [Command:Registry]."
                );
            }

            $key = substr($arg, 2); // remove leading "--"

            if ($key === '') {
                throw new ConsoleRuntimeException(
                    "Invalid flag '--'. [Command:Registry]."
                );
            }

            // forbid --flag=value in this strict mode (optional; remove if you want to allow it)
            if (str_contains($key, '=')) {
                throw new ConsoleRuntimeException(
                    "Invalid option '{$arg}'. '--flag=value' is not supported. Use '--flag'. [Command:Registry]."
                );
            }

            $params[$key] = true;
        }

        return $command->execute($params);
    }

    /**
     * Finds the class whose static default property $name matches commandName.
     * @return class-string<CommandInterface>
     */
    private function resolveCommandClass(string $commandName): string
    {
        foreach ($this->commands as $class) {
            if (!property_exists($class, 'name')) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (!$reflection->hasProperty('name')) {
                continue;
            }

            $property = $reflection->getProperty('name');

            if (!$property->isDefault()) {
                continue;
            }

            $value = $property->getDefaultValue();

            if (is_string($value) && $value === $commandName) {
                return $class;
            }
        }

        throw new ConsoleRuntimeException(
            "Unknown command '{$commandName}'. [Command:Registry]."
        );
    }
}

