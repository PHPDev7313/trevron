<?php

namespace JDS\Dbal\Example;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JDS\Contracts\Dbal\Example\SchemaExtensionInterface;
use JDS\Dbal\DataMapper;

class SchemaManager
{
    /**
     * @var SchemaExtensionInterface[]
     */
    private array $extensions = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly DataMapper $dataMapper
    ) {
    }

    /**
     * Register a schema extension
     */
    public function registerExtension(SchemaExtensionInterface $extension): self
    {
        $this->extensions[] = $extension;
        return $this;
    }

    /**
     * Check if the default schema exists
     * 
     * @throws Exception
     */
    public function schemaExists(): bool
    {
        // Check if core tables exist
        $database = $this->connection->getDatabase();

        // Check for users table as an indicator of schema existence
        return $this->dataMapper->checkTableExists($database, 'users');
    }

    /**
     * Create the default schema and apply extensions
     * 
     * @throws Exception
     */
    public function createSchema(): void
    {
        // Create default schema
        $defaultSchema = new DefaultSchema($this->connection);
        $defaultSchema->create();
    }

    /**
     * Drop the default schema
     * 
     * @throws Exception
     */
    public function dropSchema(): void
    {
        // Drop default schema
        $defaultSchema = new DefaultSchema($this->connection);
        $defaultSchema->drop();
    }

    /**
     * Initialize the schema if it doesn't exist
     * Note: Schema creation will only work from the command line
     * 
     * @throws Exception
     */
    public function initializeSchema(): void
    {
        if (!$this->schemaExists()) {
            // Only attempt to create schema if running from CLI
            if (PHP_SAPI === 'cli') {
                $this->createSchema();
            } else {
                // Just log a message if not in CLI mode
                echo "Schema does not exist. Use the command line to create it: php /bin/default database:migrations:initialize";
            }
        }
    }
}
