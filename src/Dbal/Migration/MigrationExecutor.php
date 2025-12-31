<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 31, 2025
 *
 * Execution layer for database migrations.
 * See: DatabaseMigrations.v1.2.FINAL.md
 */

declare(strict_types=1);

namespace JDS\Dbal\Migration;

use Doctrine\DBAL\Connection;
use JDS\Contracts\Dbal\Migration\MigrationExecutorInterface;
use JDS\Contracts\Dbal\Migration\MigrationLocationInterface;
use JDS\Contracts\Dbal\Migration\MigrationRepositoryInterface;
use JDS\Exceptions\Database\DatabaseRuntimeException;
use JDS\Exceptions\Database\MigrationRuntimeException;
use Throwable;

class MigrationExecutor implements MigrationExecutorInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MigrationLocationInterface $locator,
        private readonly MigrationRepositoryInterface $repository,
        private readonly string $migrationsPath
    ) {}

    public function migrateUp(): void
    {
        $this->repository->ensureStorage();

        foreach ($this->locator->all() as $migration) {
            if ($this->repository->has($migration)) {
                continue;
            }

            $this->runMigration('up', $migration);
            $this->repository->markApplied($migration);
        }
    }

    public function migrateUpTo(int $number): void
    {
        $this->repository->ensureStorage();

        foreach ($this->locator->all() as $migration) {
            if ($this->extractNumber($migration) !== $number) {
                continue;
            }

            if ($this->repository->has($migration)) {
                throw new MigrationRuntimeException(
                    "Migration already applied: {$migration}");
            }

            $this->runMigration('up', $migration);
            $this->repository->markApplied($migration);
            return;
        }

        throw new MigrationRuntimeException(
            "Migration not found for number: {$number}"
        );
    }

    public function migrateDown(): void
    {
        $applied = array_reverse($this->repository->applied());

        foreach ($applied as $migration) {
            $this->runMigration('down', $migration);
            $this->repository->markRolledBack($migration);
        }
    }

    public function migrateDownTo(int $number): void
    {
        $applied = array_reverse($this->repository->applied());

        foreach ($applied as $migration) {
            if ($this->extractNumber($migration) !== $number) {
                continue;
            }
        }

        throw new MigrationRuntimeException(
            "Applied migration not found for rollback: {$number}"
        );
    }

    private function runMigration(string $direction, string $migration): void
    {
        $path = rtrim($this->migrationsPath, '/') . '/' . $migration;

        if (!file_exists($path)) {
            throw new MigrationRuntimeException(
                "Migration file not found: {$migration}"
            );
        }

        $migrationObject = require $path;

        if (!is_object($migrationObject) || !method_exists($migrationObject, $direction)) {
            throw new MigrationRuntimeException(
                "Migration {$migration} does not implement '{$direction}' method."
            );
        }

        try {
            $migrationObject->{$direction}($migration, $this->connection);
        } catch (Throwable $e) {
            throw new MigrationRuntimeException(
                "Migration {$migration} failed during '{$direction}' execution.",
                previous: $e
            );
        }
    }

    private function extractNumber(string $file): int
    {
        if (!preg_match('/^m(\d+)_/', $file, $matches)) {
            throw new MigrationRuntimeException(
                "Invalid migration filename format: {$file}"
            );
        }

        return (int) $matches[1];
    }

    public function execute(string $direction, string $migrationFile): void
    {
        $path = $this->migrationsPath . '/' . $migrationFile;

        if (!is_file($path)) {
            throw new DatabaseRuntimeException(
                "Migration file not found: {$migrationFile}"
            );
        }

        $migration = require $path;

        if (!is_object($migration)) {
            throw new DatabaseRuntimeException(
                "Migration {$migrationFile} does not support '{$direction}'."
            );
        }

        if (!method_exists($migration, $direction)) {
            throw new DatabaseRuntimeException(
                "Migration {$migrationFile} does not support '{$direction}'."
            );
        }

        $this->connection->beginTransaction();

        try {
            $migration->{$direction}($migrationFile, $this->connection);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw new DatabaseRuntimeException(
                "Migration execution failed: {$migrationFile}",
                previous: $e
            );
        }
    }
}

