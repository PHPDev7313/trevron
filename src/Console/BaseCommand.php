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

