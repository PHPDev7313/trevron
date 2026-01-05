<?php

namespace JDS\Console;

use JDS\Error\StatusCode;
use JDS\Exceptions\Console\ConsoleException;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;

class Application
{
	public function __construct(private readonly Container $container)
	{
	}

	public function run(): int
	{
        try {
            // use environment variables to obtain the command name
            $argv = $_SERVER['argv'] ?? [];

            if (empty($argv[1])){
                throw new ConsoleException("A command name must be provided.");
            }

            $commandName = $argv[1];

            //
            // global help
            //
            if ($commandName === '--help' || $commandName === '-h') {
                $this->displayGeneralHelp();
                return 0;
            }

            //
            // fetch command from container
            // use command name to obtain a command object from the container
            //
            $command = $this->container->get($commandName);

            //
            // parse variables to obtain options and args
            //
            $args = array_slice($argv, 2);
            $options = $this->parseOptions($args);

            //
            // execute the command, returning the status code
            //
            return $command->execute($options);

        } catch (ConsoleException $e) {
            $code = StatusCode::CONSOLE_COMMAND_REGISTRATION_FAILED;
            ErrorProcessor::process($e, $code, $e->getMessage());
            return $code->value;
        } catch (\Throwable $e) {
            $code = StatusCode::CONSOLE_KERNEL_PROCESSOR_ERROR;
            ErrorProcessor::process($e, $code, "An Unexpected error occurred.");
            return $code->value;
        }
	}

    /**
     * Parse CLI options of the form:
     * --key or --key=value
     */
	private function parseOptions(array $args): array {
		$options = [];
		foreach ($args as $arg) {
            if (!str_starts_with($arg, '--')) {
                continue;
            }

            [$key, $value] = array_pad(
                explode('=', substr($arg, 2), 2),
                2,
                null
            );

            if ($key === '') {
                throw new ConsoleException("Option name is missing.");
            }

            //
            // Do NOT enforce integer validation here - that belongs to the command layer
            //
            $options[$key] = $value ?? true;
        }
        return $options;
	}

    private function displayGeneralHelp(): void
    {
        echo "Usage: php console <command> [options]";
        echo "Use --help after any command to see specific usage.\n";
    }
}

