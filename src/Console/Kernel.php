<?php

namespace JDS\Console;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Console\ConsoleRuntimeException;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;
use ReflectionClass;
use Throwable;

final class Kernel
{

	public function __construct(
		private Container $container,
		private Application $application
	)
	{
	}

    /**
     * Entry point for console execution
     */
	public function handle(): int
	{
		// register commands with the container
		$this->registerCommandFromRegistry();
//        $this->registerUserCommands(); // future expansion hook

		// run the console application, returning a status code
		return $this->application->run();
    }

    /**
     * Validate and register a discovered command.
     */
    private function registerCommandClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new ConsoleRuntimeException("Command class does not exist: {$class}. [Console:Kernel].");
        }

        if (!is_subclass_of($class, CommandInterface::class)) {
            //
            // silently ignore classes that are not commands
            //
            return;
        }

        $name = $this->resolveCommandName($class);

        //
        // Register into DI container as an alias to the real command service.
        // This ensures that commands with constructor args (like secrets commands)
        // still resolve correctly via their existing definitions.
        //
        $this->container->add($name, function () use ($class) {
            return $this->container->get($class);
        });
    }

    /**
     * Extract the "name" property that defines the console command name.
     */
    private function resolveCommandName(string $class): string
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->hasProperty('name')) {
            throw new ConsoleRuntimeException("Command {$class} is missing required 'name' property. [Console:Kernel].");
        }

        $property = $reflection->getProperty('name');

        if (!$property->isDefault()) {
            throw new ConsoleRuntimeException("Command {$class} 'name' must have default value. [Console:Kernel].");
        }

        $value = $property->getDefaultValue();

        if (!is_string($value)) {
            throw new ConsoleRuntimeException("Command {$class} 'name' must be a string. [Console:Kernel].");
        }

        if (str_contains($value, ' ')) {
            throw new ConsoleRuntimeException("Command {$class} 'name' cannot contain spaces ('{$value}'). Use single token names like 'secret:encrypt'. [Console:Kernel].");
        }

        return $value;
    }

    private function registerCommandFromRegistry(): void
    {
        if (!$this->container->has(CommandRegistryInterface::class)) {
            // Console may be intentionally disabled
            return;
        }

        $registry = $this->container->get(CommandRegistryInterface::class);

        foreach ($registry->all() as $commandClass) {
            try {
                $this->registerCommandClass($commandClass);
            } catch (Throwable $e) {
                $exitCode = 8;
                ErrorProcessor::process(
                    $e,
                    $exitCode,
                    "Failed to register command: {$commandClass}. Please contact admin. [Console:Kernel]."
                );
            }
        }
    }
}

