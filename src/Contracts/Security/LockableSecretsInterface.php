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
 * See: Secrets.v1.2.FINAL.md
 *
  * SECURITY CRITICAL PHASE
 *
 * Locks all secrets for runtime.
 * Must execute exactly once.
 * Must execute after secrets service registration.
 * Must never be bypassed.
*/

namespace JDS\Contracts\Security;

interface LockableSecretsInterface extends SecretsInterface
{
    public function lock(): void;
    public function isLocked(): bool;
}

