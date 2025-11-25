<?php

namespace JDS\Console;

use JDS\Authentication\RuntimeException;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Processing\ErrorProcessor;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Throwable;

final class Kernel
{

	public function __construct(
		private ContainerInterface $container,
		private Application $application
	)
	{
	}

	public function handle(): int
	{
		// register commands with the container
		$this->registerCommands();

		// run the console application, returning a status code
		$status = $this->application->run();

		// return the status code
		return $status;
	}

	private function registerCommands(): void
	{
        try {
            // === register all built in commands ===

            // get all files in the commands directory
            $commandDirectory = __DIR__ . '/Command';
            if (!is_dir($commandDirectory)) {
                throw new RuntimeException("Command directory does not exist: {$commandDirectory}. Please contact admin.");
            }
            $commandFiles = new \DirectoryIterator($commandDirectory);

            // this sets up command where we can use the command in the terminal
            // get the base namespace for commands from the container
            $namespace = $this->container->get('base-commands-namespace');
            if (!is_string($namespace)) {
                throw new ConsoleUnexpectedValueException("Invalid base namespace provided for commands.");
            }

            // iterate through files in the commands folder
            foreach ($commandFiles as $commandFile) {

                // check if it is NOT a file
                if (!$commandFile->isFile()) {
                    continue;
                }
                $found = array_filter(['Interface', 'Exception'], fn ($needle) => str_contains($commandFile->getFilename(), $needle));
                if (!empty($found)) {
                    continue;
                }

                try {

                    // get the command class name PSR-4 standards
                    // this will be same as filename
                    $commandClass = $namespace . pathinfo($commandFile->getFilename(), PATHINFO_FILENAME);

                    // make sure the class exists before proceeding
                    if (!class_exists($commandClass)) {
                        throw new ConsoleRuntimeException("Command class does not exist: {$commandClass}. Please contact admin.");
                    }

                    // check if the command class implements CommandInterface
                    if (is_subclass_of($commandClass, CommandInterface::class)) {
                        // add to the container, using the name as the ID e.g. $container->add
                        // ('database:migrations:migrate', MigrateDatabase::class)
                        // retrieve the command's 'name' property using reflection
                        $reflectionClass = new ReflectionClass($commandClass);
                        if (!$reflectionClass->hasProperty('name')) {
                            throw new ConsoleRuntimeException("Command class {$commandClass} is missing the required 'name' property. Please contact admin.");
                        }
                        $nameProperty = $reflectionClass->getProperty('name');
                        if (!$nameProperty->isDefault()) {
                            throw new ConsoleRuntimeException("The 'name' property of command class {$commandClass} must have a default value. Please contact admin.");
                        }
                        $commandName = $nameProperty->getDefaultValue();
                        if (!is_string($commandName)) {
                            throw new ConsoleUnexpectedValueException("The 'name' property in {$commandClass} must be a string. Please contact admin.");
                        }
                        // add the command class to the container
                        $this->container->add($commandName, $commandClass);
                    }
                } catch (Throwable $e) {
                    // handle exceptions for individual command files
                    $exitCode = 8;
                    ErrorProcessor::process(
                        $e,
                        $exitCode,
                        "Failed to register command: {$commandFile->getFilenme()}. Please contact admin."
                    );
                }
            }
        } catch (Throwable $e) {
            // handle errors for the entire registration process using appMode
            $exitCode = 1109;
            ErrorProcessor::process($e, $exitCode, "Unknown error registering commands. Please contact admin."
            );

        }
        // === placeholder for registering user-defined commands ===
        // (@todo) implement
	}
}

