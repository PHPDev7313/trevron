<?php

namespace JDS\Console\Command;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Security\Secrets;
use League\Container\Container;

class BootstrapVerifyCommand implements CommandInterface
{
    protected string $name = "bootstrap:verify";
    protected string $description = "Verify console bootstrap invariants.";

    public function __construct(
        private readonly Container $container
    )
    {
    }

    public function execute(array $params = []): int
    {
        // CONFIG
        if (!$this->container->has('config')) {
            throw new BootstrapInvariantViolationException("Config missing.");
        }

        // SECRETS
        if (!$this->container->has(Secrets::class)) {
            throw new BootstrapInvariantViolationException("Secrets not available.");
        }

        // COMMAND REGISTRY
        if (!$this->container->has(CommandRegistryInterface::class)) {
            throw new BootstrapInvariantViolationException("Command registry missing.");
        }

        // Attempt safe resolution
        $this->container->get(Secrets::class);

        echo "✔ Console bootstrap verified successfully.\n";
        return 0;
    }
}