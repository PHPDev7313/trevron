<?php

namespace JDS\Console;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Exceptions\Console\ConsoleInvalidArgumentException;

abstract class BaseCommand implements CommandInterface
{
    /**
     * Console command name (single token, e.g. "secret:encrypt").
     */
    protected string $name = '';

    /**
     * Optional human-readable description.
     */
    protected string $description = '';

    protected array $arguments = [];

    protected array $options = [
        'help' => 'Show help for this command'
    ];

    /**
     * All commands MUST implement execute() because of CommandInterface.
     *
     * Your child classes will implement real logig inside execute(),
     * but can call $this->helpRequest($parms) to auto-display help.
     */
    abstract public function execute(array $params = []): int;

    protected function helpRequested(array $params = []):bool
    {
        return isset($params['help']) || isset($params['h']);
    }

    protected function printHelp(): void
    {
        echo "Command: {$this->name}\n";

        if ($this->description) {
            echo "{$this->description}\n\n";
        }

        if (!empty($this->arguments)) {
            echo "Arguments:\n";
            foreach ($this->arguments as $arg => $desc) {
                echo sprintf("  %-15s %s\n", $arg, $desc);
            }
            echo "\n";
        }

        if (!empty($this->options)) {
            echo "Options:\n";
            foreach ($this->options as $opt => $desc) {
                echo sprintf("  --%-12s %s\n", $opt, $desc);
            }
            echo "\n";
        }

        echo "Usage:\n";
        echo "  php bin/console {$this->name} [options]\n\n";
    }

    protected function line(string $msg): void
    {
        echo $msg. PHP_EOL;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function writeln(string $message): void
    {
        echo $message . PHP_EOL;
    }

    //❗ Leave this alone (correct as-is)
    protected function error(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }

    protected function requireOption(array $params, string $key, ?string $errorMessage = null): string
    {
        if (!array_key_exists($key, $params) || $params[$key] === null || $params[$key] === '') {
            $msg = $errorMessage ?? "Missing required option --{$key}";
            $this->error($msg);
            throw new ConsoleInvalidArgumentException($msg);
        }
        return (string) $params[$key];
    }
}

