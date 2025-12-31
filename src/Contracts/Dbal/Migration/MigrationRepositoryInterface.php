<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 31, 2025
 *
 * Contract for migration discovery and state tracking.
 */

declare(strict_types=1);

namespace JDS\Contracts\Dbal\Migration;

interface MigrationRepositoryInterface
{

    /**
     * Ensure the migrations table exists.
     */
    public function ensureStorage(): void;

    /**
     * @return list<string> Migration filenames already applied
     */
    public function applied(): array;

    /**
     * Mark a migration as applied.
     */
    public function markApplied(string $migration): void;

    /**
     * Remove a migration from applied list.
     */
    public function markRolledBack(string $migration): void;

    /**
     * Check if a migration has already been applied.
     */
    public function has(string $migration): bool;
}

