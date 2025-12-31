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

namespace JDS\Dbal\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use JDS\Contracts\Dbal\Migration\MigrationRepositoryInterface;

class MigrationRepository implements MigrationRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function ensureStorage(): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['migrations'])) {
            return;
        }

        $schema = new Schema();
        $table = $schema->createTable('migrations')
            ->addOption('engine', 'InnoDB');

        $table->addColumn('id', Types::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);

        $table->addColumn('migration', Types::STRING, [
            'length' => 255,
        ]);

        $table->addColumn('created_at', Types::DATE_IMMUTABLE, [
            'default' => 'CURRENT_TIMESTAMP',
        ]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addUniqueIndex(['migration']);

        foreach ($schema->toSql($this->connection->getDatabasePlatform()) as $sql) {
            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @inheritDoc
     */
    public function applied(): array
    {
        return $this->connection
            ->executeQuery(
                "SELECT migration FROM migrations ORDER BY migration"
            )
            ->fetchFirstColumn();
    }

    /**
     * @inheritDoc
     */
    public function has(string $migration): bool
    {
        return (bool) $this->connection
            ->executeQuery(
                "SELECT 1 FROM migrations WHERE migration = ? LIMIT 1",
                [$migration]
            )
            ->fetchOne();
    }

    /**
     * @inheritDoc
     */
    public function markApplied(string $migration): void
    {
        $this->connection->executeStatement(
            "INSERT INTO migrations (migration) VALUES (?)",
            [$migration]
        );
    }

    /**
     * @inheritDoc
     */
    public function markRolledBack(string $migration): void
    {
        $this->connection->executeStatement(
            "DELETE FROM migrations WHERE migration = ?",
            [$migration]
        );
    }
}

