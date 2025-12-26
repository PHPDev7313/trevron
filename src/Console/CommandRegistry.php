<?php

namespace JDS\Console;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Console\ConsoleRuntimeException;

class CommandRegistry implements CommandRegistryInterface
{
    /** @var class-string<CommandInterface>[] */
    private array $commands = [];

    public function register(string $commandClass): void
    {
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

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->commands;
    }
}

