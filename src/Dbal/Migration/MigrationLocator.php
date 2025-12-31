<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 31, 2025
 */

declare(strict_types=1);

namespace JDS\Dbal\Migration;

use JDS\Contracts\Dbal\Migration\MigrationLocationInterface;
use JDS\Exceptions\Database\MigrationRuntimeException;

class MigrationLocator implements MigrationLocationInterface
{
    public function __construct(
        private readonly string $migrationsPath
    )
    {
        if (!is_dir($this->migrationsPath)) {
            throw new MigrationRuntimeException(
                "Migrations path does not exist: {$migrationsPath}"
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        $files = scandir($this->migrationsPath);

        if ($files === false) {
            throw new MigrationRuntimeException(
                "Failed to read migrations directory: {$this->migrationsPath}"
            );
        }

        $migrations = [];

        foreach ($files as $file) {
            $number = $this->extractNumber($file);
            $migrations[$number] = $file;
        }

        // Numeric ordering - NEVER filesystem order
        ksort($migrations, SORT_NUMERIC);

        return array_values($migrations);
    }

    private function isMigrationFile(string $file): bool
    {
        return (bool) preg_match(
            '/^m\d+_.+\.php$/',
            $file
        );
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
}

