<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 27, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 */

namespace JDS\Contracts\Security;

interface SecretsInterface
{
    /**
     * Get a secret by dot-notated path, e.g. "db.user", "jwt.access".
     */
    public function get(string $path, mixed $default = null): mixed;

    /**
     * Get all secrets as an array (use sparingly).
     */
    public function all(): array;

    public function has(string $path): bool;

}

