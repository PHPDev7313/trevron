<?php

namespace JDS\Contracts\Dbal\Migration;

interface MigrationExecutorInterface
{
    /**
     * Run all pending migrations in numeric order.
     */
    public function migrateUp(): void;

    /**
     * Run a single migration by numeric identifier (e.g. 1, 999000).
     */
    public function migrateUpTo(int $number): void;

    /**
     * Roll back all applied migrations in reverse numeric order.
     */
    public function migrateDown(): void;

    /**
     * Roll back a single migration by numeric identifier.
     */
    public function migrateDownTo(int $number): void;
}

