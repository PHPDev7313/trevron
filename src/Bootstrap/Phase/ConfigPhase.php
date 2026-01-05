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

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

class ConfigPhase implements BootstrapPhaseInterface
{

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::CONFIG;
    }

    public function bootstrap(Container $container): void
    {
        // Config already loaded before container creation.
        // Phase exists purely for ordering + invariants.
        $this->assertEnv(
            'APP_ENV',
            'APP_SECRET_KEY',
            'SECRETS_FILE',
            'SECRETS_PLAIN',
            'SCHEMA_FILE'
        );
    }

    private function assertEnv(string ...$keys): void
    {
        foreach ($keys as $key) {
            if (!isset($_ENV[$key]) || trim($_ENV[$key]) === '') {
                throw new BootstrapInvariantViolationException(
                    "Missing required environment variable: {$key}"
                );
            }
        }
    }
}

