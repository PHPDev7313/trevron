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
        fwrite(STDOUT, "Command: {$this->name}\n");

        if ($this->description) {
            fwrite(STDOUT, "{$this->description}\n\n");
        }

        if (!empty($this->arguments)) {
            fwrite(STDOUT, "Arguments:\n");
            foreach ($this->arguments as $arg => $desc) {
                fwrite(STDOUT, sprintf("  %-15s %s\n", $arg, $desc));
            }
            fwrite(STDOUT, "\n");
        }

        if (!empty($this->options)) {
            fwrite(STDOUT, "Options:\n");
            foreach ($this->options as $opt => $desc) {
                fwrite(STDOUT, sprintf("  --%-12s %s\n", $opt, $desc));
            }
            fwrite(STDOUT, "\n");
        }

        fwrite(STDOUT, "Usage:\n");
        fwrite(STDOUT, "  php bin/console {$this->name} [options]\n");
        fwrite(STDOUT, "\n");
    }

    protected function line(string $msg): void
    {
        fwrite(STDOUT, $msg. PHP_EOL);
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
        fwrite(STDOUT, $message . PHP_EOL);
    }

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

