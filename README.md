# Trevron
Basic framework with authorization, events, migrations, middleware, csrf tokens, and database schema management.

## Only to be used by [JDS](https://jessdigisys.com) (Jessop Digital Systems) or for Educational Purposes

# NOT PRODUCTION USE! || !$productionUse

## Features

- Authentication and Authorization
- Event Dispatching
- Database Migrations
- Middleware
- CSRF Protection
- Database Schema Management

## Database Schema Management

The framework includes a database schema management system that provides:

- A default schema with tables for users, roles, permissions, menus, and related mapping tables
- A mechanism for extending the schema with client-specific tables
- A console command to initialize or drop the schema

### Usage

```php
// Get the schema manager from the container
$schemaManager = $container->get(SchemaManager::class);

// Initialize the schema (creates it if it doesn't exist)
// Note: This will only check if the schema exists and won't create it if not running from CLI
$schemaManager->initializeSchema();
```

### Command Line Usage

Schema creation and dropping operations can **only** be performed from the command line on the client side for security reasons:

```bash
# Initialize the schema (create it if it doesn't exist)
php /bin/default database:migrations:initialize

# Initialize the schema with explicit up option
php /bin/default database:migrations:initialize --up

# Drop the schema
php /bin/default database:migrations:initialize --down
```

Attempting to create or drop the schema programmatically outside of the command line will result in a RuntimeException.

For more details, see the [Schema Management Documentation](src/Dbal/Example/README.md).
