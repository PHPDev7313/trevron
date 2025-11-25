<?php

namespace JDS\Console\Commands;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Dbal\Schema\SchemaManager;

class InitSchemaCommand implements CommandInterface
{
    public static string $name = 'schema:init';

    public function __construct(private readonly SchemaManager $schemaManager)
    {
    }

    public function execute(array $params = []): int
    {
        try {
            echo "Database Schema Initialization\n";

            if ($this->schemaManager->schemaExists()) {
                echo "Database schema already exists.\n";
                return 0;
            }

            echo "Creating database schema...\n";
            $this->schemaManager->createSchema();
            echo "Database schema created successfully.\n";

            return 0;
        } catch (\Exception $e) {
            echo "Failed to initialize database schema: " . $e->getMessage() . "\n";
            return 1;
        }
    }
}
