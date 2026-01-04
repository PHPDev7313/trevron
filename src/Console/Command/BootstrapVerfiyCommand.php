<?php

namespace JDS\Console\Command;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapResolutionNotAllowedException;
use League\Container\Container;

class BootstrapVerfiyCommand implements CommandInterface
{
    public static string $name = "bootstrap:verfiy";

    public function __construct(
        private readonly Container $container
    )
    {
    }

    public function execute(array $params = []): int
    {
        try {
            $this->verifyConfig();
            $this->verifySecrets();
            $this->verifyCommand();

            echo "✔ Console bootstrap invariants verified." . PHP_EOL;
            return 0;

        } catch (BootstrapResolutionNotAllowedException $e) {
            fwrite(STDERR, "✘ BOOTSTRAP VIOLATION: {$e->getMessage()}" . PHP_EOL);
            return 2;
        }
    }

    private function verifyConfig(): void
    {
        if (!$this->container->has('config')) {
            throw new BootstrapResolutionNotAllowedException(
                "CONFIG invariant failed: 'config' missing. [Bootstrap:Verify:Command]."
            );
        }

        $config = $this->container->get('config');

        foreach (['app', 'database', 'secrets'] as $section) {
            if (!isset($config[$section])) {
                throw new BootstrapResolutionNotAllowedException(
                    "CONFIG invariant failed: missing '{$section}' section. [Bootstrap:Verify:Command]."
                );
            }
        }
    }

    // -------------------------------------------------
    // SECRETS
    // -------------------------------------------------

    private function verifySecrets(): void
    {
        if (!$this->container->has(SecretsInterface::class)) {
            throw new BootstrapResolutionNotAllowedException(
                "SECRETS invariant failed: SecretsInterface missing. [Bootstrap:Verify:Command]."
            );
        }

        // INTENTIONAL resolution: validates crypto + schema
        $this->container->get(SecretsInterface::class);
    }

    // ------------------------------------------------------
    // COMMANDS
    // ------------------------------------------------------

    private function verifyCommand(): void
    {
        if (!$this->container->has(CommandRegistryInterface::class)) {
            throw new BootstrapResolutionNotAllowedException(
                "COMMANDS invariant failed: CommandRegistryInterface missing. [Bootstrap:Verify:Command]."
            );
        }

        $registry = $this->container->get(CommandRegistryInterface::class);

        if (!method_exists($registry, 'isLocked') || !$registry->isLocked()) {
            throw new BootstrapResolutionNotAllowedException(
                "COMMANDS invariant failed: Command registry is not locked. [Bootstrap:Verify:Command]."
            );
        }

        if (count($registry->all()) === 0) {
            throw new BootstrapResolutionNotAllowedException(
                "COMMANDS invariant failed: No commands registered. [Bootstrap:Verify:Command]."
            );
        }
    }
}

