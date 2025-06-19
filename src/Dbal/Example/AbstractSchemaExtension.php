<?php

namespace JDS\Dbal\Example;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

abstract class AbstractSchemaExtension implements SchemaExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function extend(Schema $schema): void
    {
        $this->extendSchema($schema);
    }

    /**
     * Extend the schema with additional tables or modifications
     * This method should be implemented by concrete extension classes
     */
    abstract protected function extendSchema(Schema $schema): void;

    /**
     * Helper method to create a table if it doesn't exist
     */
    protected function createTableIfNotExists(Schema $schema, string $tableName): Table
    {
        if ($schema->hasTable($tableName)) {
            return $schema->getTable($tableName);
        }
        
        return $schema->createTable($tableName);
    }

    /**
     * Helper method to add a column if it doesn't exist
     */
    protected function addColumnIfNotExists(Table $table, string $columnName, string $type, array $options = []): void
    {
        if (!$table->hasColumn($columnName)) {
            $table->addColumn($columnName, $type, $options);
        }
    }

    /**
     * Helper method to add a foreign key if it doesn't exist
     */
    protected function addForeignKeyIfNotExists(
        Table $table, 
        string $foreignTable, 
        array $localColumnNames, 
        array $foreignColumnNames, 
        array $options = []
    ): void {
        // Generate a name for the foreign key
        $name = $this->generateForeignKeyName($table->getName(), $localColumnNames, $foreignTable, $foreignColumnNames);
        
        // Check if the foreign key already exists
        foreach ($table->getForeignKeys() as $foreignKey) {
            if ($foreignKey->getName() === $name) {
                return;
            }
        }
        
        // Add the foreign key
        $table->addForeignKeyConstraint($foreignTable, $localColumnNames, $foreignColumnNames, $options, $name);
    }

    /**
     * Generate a name for a foreign key
     */
    private function generateForeignKeyName(
        string $localTable, 
        array $localColumns, 
        string $foreignTable, 
        array $foreignColumns
    ): string {
        return sprintf(
            'fk_%s_%s_%s_%s',
            $localTable,
            implode('_', $localColumns),
            $foreignTable,
            implode('_', $foreignColumns)
        );
    }
}