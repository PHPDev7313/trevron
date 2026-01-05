<?php
/*
 * Trevron Framework — v1.2 FINAL
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
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapCommandException;
use League\Container\Container;

final class BootstrapDumpCommand implements CommandInterface
{
    public static string $name = "bootstrap:dump";

    public function __construct(
        private readonly Container $container
    )
    {
    }

    public function execute(array $params = []): int
    {
        $data = [
            'runtime' => 'console',
            'phase' => [
                'CONFIG',
                'SECRETS',
                'COMMANDS',
            ],
            'invariants' => [
                'config' => $this->container->get('config'),
                'secrets' => $this->container->has(SecretsInterface::class),
                'commands' => $this->container->has(CommandRegistryInterface::class),
            ],
            'command_registry' => $this->dumpRegistry(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        echo $json . PHP_EOL;

        return 0;
    }

    private function dumpRegistry(): array
    {
        if (!$this->container->has(CommandRegistryInterface::class)) {
            throw new BootstrapCommandException(
                "Command Registry Interface missing during bootstrap dump."
            );
        }

        $registry = $this->container->get(CommandRegistryInterface::class);

        $commands = [];

        foreach ($registry->all() as $commandClass) {
            if (!property_exists($commandClass, 'name')) {
                continue;
            }

            $commands[] = $commandClass::$name;
        }
        sort($commands);

        return [
            "locked" => method_exists($registry, 'isLocked')
            ? $registry->isLocked()
                : null,
            'commands' => $commands,
        ];
    }
}

