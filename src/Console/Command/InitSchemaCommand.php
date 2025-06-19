<?php

namespace JDS\Console\Command;

use JDS\Dbal\Schema\SchemaManager;

class InitSchemaCommand implements CommandInterface
{
    public static string $name = 'database:migrations:initialize';

    public function __construct(private readonly SchemaManager $schemaManager)
    {
    }

    public function execute(array $params = []): int
    {
        try {
            echo "Database Schema Initialization\n";

            // Check for up/down options
            $up = $params['up'] ?? false;
            $down = $params['down'] ?? false;

            // If no option is provided, default to up
            if (!$up && !$down) {
                $up = true;
            }

            // Handle down option (drop schema)
            if ($down) {
                if (!$this->schemaManager->schemaExists()) {
                    echo "Database schema does not exist, nothing to drop.\n";
                    return 0;
                }

                echo "Dropping database schema...\n";
                $this->schemaManager->dropSchema();
                echo "Database schema dropped successfully.\n";
                return 0;
            }

            // Handle up option (create schema)
            if ($up) {
                if ($this->schemaManager->schemaExists()) {
                    echo "Database schema already exists.\n";
                    return 0;
                }

                echo "Creating database schema...\n";
                $this->schemaManager->createSchema();
                echo "Database schema created successfully.\n";
                return 0;
            }

            return 0;
        } catch (\Exception $e) {
            echo "Failed to initialize database schema: " . $e->getMessage() . "\n";
            return 1;
        }
    }
}
