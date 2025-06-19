# Database Schema Management

This module provides a system for managing database schemas in the Trevron framework. It includes a default schema that is created automatically and can be extended with client-specific tables and modifications.

## Components

### DefaultSchema

The `DefaultSchema` class defines the default database schema with the following tables:

- `users`: Stores user information (id, user_id, firstname, lastname, email, etc.)
- `roles`: Stores role definitions (id, role_id, name, description, etc.)
- `permissions`: Stores permission definitions (id, permission_id, name, description, bitwise, etc.)
- `menus`: Stores menu items (id, menu_id, name, url, controller, etc.)
- `smenus`: Stores sub-menu items (id, smenu_id, name, url, controller, etc.)
- `tmenus`: Stores tertiary menu items (id, tmenu_id, name, url, controller, etc.)
- `user_role`: Maps users to roles (many-to-many relationship)
- `user_permission`: Maps users to permissions (many-to-many relationship)
- `menu_role`: Maps menus to roles (many-to-many relationship)
- `menu_smenu`: Maps menus to sub-menus (many-to-many relationship)
- `tmenu_smenu`: Maps tertiary menus to sub-menus (many-to-many relationship)

### SchemaManager

The `SchemaManager` class manages the database schema. It provides methods to:

- Check if the schema exists
- Create the schema
- Drop the schema
- Initialize the schema if it doesn't exist
- Register schema extensions

### SchemaExtensionInterface

The `SchemaExtensionInterface` defines the contract for schema extensions. Extensions must implement the `extend` method to add tables or modify the schema.

### AbstractSchemaExtension

The `AbstractSchemaExtension` class provides a base implementation for schema extensions with helper methods for common operations:

- Creating tables if they don't exist
- Adding columns if they don't exist
- Adding foreign keys if they don't exist

## Usage

### Basic Usage

```php
// Get the schema manager from the container
$schemaManager = $container->get(SchemaManager::class);

// Initialize the schema (creates it if it doesn't exist)
$schemaManager->initializeSchema();
```

### Command Line Usage

The framework provides a command line interface for managing the database schema. Note that schema creation and dropping operations can **only** be performed from the command line on the client side for security reasons:

```bash
# Initialize the schema (create it if it doesn't exist)
php /bin/default database:migrations:initialize

# Initialize the schema with explicit up option
php /bin/default database:migrations:initialize --up

# Drop the schema
php /bin/default database:migrations:initialize --down
```

Attempting to create or drop the schema programmatically outside of the command line will result in a RuntimeException.

### Extending the Schema

To extend the schema with client-specific tables, create a class that extends `AbstractSchemaExtension`:

```php
use JDS\Dbal\Schema\AbstractSchemaExtension;
use Doctrine\DBAL\Schema\Schema;

class ClientSchemaExtension extends AbstractSchemaExtension
{
    protected function extendSchema(Schema $schema): void
    {
        // Create a new table
        $table = $this->createTableIfNotExists($schema, 'client_data');

        // Add columns
        $this->addColumnIfNotExists($table, 'id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $this->addColumnIfNotExists($table, 'user_id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'name', 'string', ['length' => 255]);
        $this->addColumnIfNotExists($table, 'created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        // Set primary key
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey(['id']);
        }

        // Add foreign key
        $this->addForeignKeyIfNotExists($table, 'users', ['user_id'], ['user_id'], ['onDelete' => 'CASCADE']);
    }
}
```

Then register the extension with the schema manager:

```php
// Create the extension
$extension = new ClientSchemaExtension();

// Register it with the schema manager
$schemaManager->registerExtension($extension);

// Initialize the schema (creates it if it doesn't exist, including the extension)
$schemaManager->initializeSchema();
```
