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
 *    : ConsoleBootstrapLifecycle.v1.2.FINAL.md
 */

namespace JDS\Bootstrap\Phase;

use JDS\Console\Application;
use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

final class CommandPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::COMMANDS;
    }

    public function bootstrap(Container $container): void
    {

        // ----------------------------------------------------------
        // CONFIG - must exist
        // ----------------------------------------------------------
        if (!$container->has('config')) {
            throw new BootstrapInvariantViolationException(
                "Command Phase invariant violation: config missing. [Command:Phase].");
        }

        $config = $container->get('config');

        foreach (['app', 'database', 'secrets'] as $section) {
            if (!isset($config[$section])) {
                throw new BootstrapInvariantViolationException(
                    "Command Phase invariant violation: config missing section '{$section}'. [Command:Phase]."
                );
            }
        }

        // -----------------------------------------------------------
        // SECRETS - must exist AND be locked
        // -----------------------------------------------------------
        if (!$container->has(SecretsInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Command Phase invariant violation: Secrets Interface missing. [Command:Phase]."
            );
        }

        // resolving here is INTENTIONAL: validates key, schema file
        $container->get(SecretsInterface::class);

        // ------------------------------------------------------------
        // COMMAND REGISTRY - must exist and be mutable
        // ------------------------------------------------------------
        if (!$container->has(CommandRegistryInterface::class)) {
            throw new BootstrapInvariantViolationException(
                "Command Phase invariant violation: Command Registry missing. [Command:Phase]."
            );
        }

        $registry = $container->get(CommandRegistryInterface::class);

        if (method_exists($registry, 'isLocked') && $registry->isLocked()) {
            throw new BootstrapInvariantViolationException(
                "Command Phase invariant violation: Command Registry is locked too early. [Command:Phase]."
            );
        }

        // ------------------------------------------------
        // APPLICATION - must exist
        // ------------------------------------------------
        if (!$container->has(Application::class)) {
            throw new BootstrapInvariantViolationException(
                "Command Phase invariant violation: Application missing. [Command:Phase]."
            );
        }

        // ------------------------------------------------
        // FINAL ACTION - Lock registry
        // ------------------------------------------------
        if (method_exists($registry, 'lock')) {
            $registry->lock();
        }
    }

}

