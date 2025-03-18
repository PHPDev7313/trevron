<?php

namespace JDS\Console;

use JDS\Processing\ErrorProcessor;
use Psr\Container\ContainerInterface;

class Application
{
	public function __construct(private readonly ContainerInterface $container)
	{
	}

	public function run(): int
	{
        try {
            // use environment variables to obtain the command name
            $argv = $_SERVER['argv'];
            $commandName = $argv[1] ?? null;

            // throw an exception if no command name is provided
            if (!$commandName) {
                throw new ConsoleException('A command name must be provided');
            }

            // use command name to obtain a command object from the container
            $command = $this->container->get($commandName);

            // parse variables to obtain options and args
            $args = array_slice($argv, 2);

            $options = $this->parseOptions($args);

            // execute the command, returning the status code
            $status = $command->execute($options);

            // return the status code
            return $status;
        } catch (ConsoleException $e) {
            $exitCode = 80;
            ErrorProcessor::process($e, $exitCode, $e->getMessage());
            return $exitCode;
        } catch (\Throwable $e) {
            $exitCode = 89;
            ErrorProcessor::process($e, $exitCode, "An Unexpected error occurred.");
            return $exitCode;
        }
	}

	private function parseOptions(array $args): array {
		$options = [];
		foreach ($args as $arg) {
            // check if the argument starts with '--' to identify it as an option
			if (str_starts_with($arg, '--')) {
				// split the option into key and value (if any)
				[$key, $value] = array_pad(explode('=', substr($arg, 2), 2), 2, null);
                // validate the option key presence
                if (empty($key)) {
                    throw new ConsoleException('Option name is missing');
                }
				// if an option has a value, ensure it follows the validation rule
				if (!is_null($value) && (int)$value < 1)  {
					throw new ConsoleException("Option  '{$key}' has an invalid value '{$value}'.");
				}
                // set the option value, 'true' is the default when no value is provided
				$options[$key] = $value ?? true;
			}
		}
		return $options;
	}
}

