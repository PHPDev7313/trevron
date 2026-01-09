<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 31, 2025
 */

declare(strict_types=1);

namespace JDS\Console\Command;

use Doctrine\DBAL\Connection;
use JDS\Console\AbstractCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Dbal\Migration\MigrationExecutorInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Database\MigrationRuntimeException;
use JDS\Processing\ErrorProcessor;
use Throwable;

final class MigrateDatabase extends AbstractCommand implements CommandInterface
{
    protected string $name = "database:migrations:migrate";

    protected string $description = "Run database migrations.";

    protected array $usage = [
        "php bin/console databae:migrations:migrate --up",
        "php bin/console databae:migrations:migrate --up=1",
        "php bin/console databae:migrations:migrate --down",
        "php bin/console databae:migrations:migrate --down=1",
    ];

    protected array $options = [
        'up'    => ['type' => 'int', 'required' => false],
        'down'  => ['type' => 'int', 'required' => false],
    ];

    public function __construct(
        private readonly MigrationExecutorInterface $executor
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }
    protected function handle(array $params): int
    {
        try {
            if (array_key_exists('up', $params)) {
                $this->runUp($params['up'] ?? null);
                return 0;
            }

            if (array_key_exists('down', $params)) {
                $this->runDown($params['down'] ?? null);
                return 0;
            }

            throw new MigrationRuntimeException(
                "You must specify either --up or --down."
            );

        } catch (Throwable $e) {
            $exitCode = StatusCode::DATABASE_MIGRATION_EXECUTION_FAILED;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "[Migration] Migration execution failed."
            );
            return $exitCode->value;
        }
    }

    private function runUp(?int $number): void
    {
        if ($number === null) {
            $this->info("running all pending migrations...");
            $this->executor->migrateUp();
            $this->info("Migrations completed.");
            return;
        }

        $this->info("Running migration m{$number}...");
        $this->executor->migrateUpTo($number);
        $this->info("Migration m{$number} applied.");
    }

    private function runDown(?int $number): void
    {
        if ($number === null) {
            $this->warn("Rolling back all migrations...");
            $this->executor->migrateDown();
            $this->warn("All migrations rolled back.");
            return;
        }

        $this->warn("Rolling back all migration m{$number}...");
        $this->executor->migrateDownTo($number);
        $this->info("Migration m{$number} rolled back.");
    }
}

