<?php

namespace JDS\Console;

use DirectoryIterator;
use JDS\Authentication\RuntimeException;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Handlers\ExceptionHandler;
use JDS\Http\StatusCodeManager;
use JDS\Logging\ExceptionLogger;
use JDS\Processing\ErrorProcessor;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ReflectionClass;
use Throwable;

final class Kernel
{

	public function __construct(
		private Container $container,
		private Application $application
	)
	{
        $this->processorValidate();
	}

    /**
     * Entry point for console execution
     */
	public function handle(): int
	{
		// register commands with the container
		$this->registerBuiltInCommands();
        $this->registerUserCommands(); // future expansion hook

		// run the console application, returning a status code
		return $this->application->run();
    }

	private function registerBuiltInCommands(): void
	{
        try {
            // === register all built in commands ===

            // get all files in the commands directory
            $directory = __DIR__ . '/Command';
            if (!is_dir($directory)) {
                throw new RuntimeException("Command directory does not exist: {$directory}. [Console:Kernel] Please contact admin.");
            }

            // this sets up command where we can use the command in the terminal
            // get the base namespace for commands from the container
            $namespace = $this->container->get('base-commands-namespace');
            if (!is_string($namespace)) {
                throw new ConsoleUnexpectedValueException("Invalid command namespace configured. [Console:Kernel].");
            }

            // iterate through files in the commands folder
            foreach ((new DirectoryIterator($directory)) as $file) {

                // check if it is NOT a file
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $class = $namespace . pathinfo($file->getFilename(), PATHINFO_FILENAME);

                try {
                    $this->registerCommandClass($class);
                } catch (Throwable $e) {
                    // handle exceptions for individual command files
                    $exitCode = 8;
                    ErrorProcessor::process(
                        $e,
                        $exitCode,
                        "[Code:{$exitCode}] Failed to register command: {$file->getFilenme()}. Please contact admin."
                    );
                }
            }
        } catch (Throwable $e) {
            // handle errors for the entire registration process using appMode
            $exitCode = 1109;
            ErrorProcessor::process($e, $exitCode, "[Code:{$exitCode}] Unknown error registering commands. Please contact admin."
            );
        }
	}

    /**
     * Validate and register a discovered command.
     */
    private function registerCommandClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new ConsoleRuntimeException("Command class does not exist: {$class}.");
        }

        if (!is_subclass_of($class, CommandInterface::class)) {
            //
            // silently ignore classes that are not commands
            //
            return;
        }

        $name = $this->resolveCommandName($class);

        //
        // Register into DI container
        //
        $this->container->add($name, $class);
    }

    /**
     * Extract the "name" property that defines the console command name.
     */
    private function resolveCommandName(string $class): string
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->hasProperty('name')) {
            throw new ConsoleRuntimeException("Command {$class} is missing required 'name' property.");
        }

        $property = $reflection->getProperty('name');

        if (!$property->isDefault()) {
            throw new ConsoleRuntimeException("Command {$class} 'name' must have default value.");
        }

        $value = $property->getDefaultValue();

        if (!is_string($value)) {
            throw new ConsoleRuntimeException("Command {$class} 'name' must be a string.");
        }

        return $value;
    }

    /**
     * Extension point: user-defined commands registered outside the built-in directory.
     */
    private function registerUserCommands(): void
    {
        /**
         * Developers can bind a list of command class names in config.
         *
         * Example:
         *   $container->add('user-commands', [
         *      \App\Console\ReindexSearch::class,
         *      \App\Console\GenerateReports::class,
         *   ]);
         */
        if (!$this->container->has('user-commands')) {
            return;
        }

        $userCommands = $this->container->get('user-commands');

        if (!is_array($userCommands)) {
            throw new ConsoleRuntimeException("Invalid 'user-commands' config. Must be array.");
        }

        foreach($userCommands as $class) {
            try {
                $this->registerCommandClass($class);
            } catch (Throwable $e) {
                $exitCode = 9;
                ErrorProcessor::process(
                    $e,
                    $exitCode,
                    "Failed to register user-defined command: {$class}. Please contact admin."
                );
            }
        }
    }

    private function processorValidate(): void
    {
        $provider = [
            ErrorProcessor::class,
            ExceptionHandler::class
        ];

        foreach ($provider as $class) {
            if (!$this->container->has($class)) {
                $this->container->add($class);
            }
        }
        $this->processLoggers();
        $this->initialize();
    }

    private function processLoggers(): void
    {
        if (!$this->container->has('loggers')) {
            $loggers = [];
            foreach ($this->container->get('config')->get('loggers') as $key => $loggerConfig) {
                $logger = new Logger($loggerConfig['name']);
                $logger->pushHandler(new StreamHandler($loggerConfig['path'], Logger::toMonologLevel($loggerConfig['level'])));
                $loggers[$key] = $logger;
            }

            $this->container->add('loggerFactory', new ArrayArgument($loggers));

            $this->container->add('ExceptionLogger', ExceptionLogger::class)
                ->addArguments([
                    $this->container->get('loggerFactory')['exception'],
                    StatusCodeManager::class,
                    $this->container->get('config')->isProduction()
                ]);
        }
    }

    private function initialize(): void
    {
        if ($this->container->has(ExceptionHandler::class)) {
            ExceptionHandler::initializeWithEnvironment($this->container->get('config')->get('environment'));
        }

        if ($this->container->has(ErrorProcessor::class && $this->container->has('ExceptionLogger'))) {
            ErrorProcessor::initialize($this->container->get('ExceptionLogger'));
        }
    }
}

