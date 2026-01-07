<?php
/*
 * Client Freelance — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 3, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

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

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
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

