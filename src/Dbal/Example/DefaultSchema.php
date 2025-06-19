<?php

namespace JDS\Dbal\Example;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

class DefaultSchema
{
    private Schema $schema;

    public function __construct(private readonly Connection $connection)
    {
        $this->schema = new Schema();
    }

    /**
     * Define the default database schema
     * 
     * @return Schema
     */
    public function define(): Schema
    {
        // Users table
        $usersTable = $this->schema->createTable('users');
        $this->defineUsersTable($usersTable);

        // Roles table
        $rolesTable = $this->schema->createTable('roles');
        $this->defineRolesTable($rolesTable);

        // Permissions table
        $permissionsTable = $this->schema->createTable('permissions');
        $this->definePermissionsTable($permissionsTable);

        // Menus table
        $menusTable = $this->schema->createTable('menus');
        $this->defineMenusTable($menusTable);

        // Sub-menus table
        $smenusTable = $this->schema->createTable('smenus');
        $this->defineSMenusTable($smenusTable);

        // Tertiary menus table
        $tmenusTable = $this->schema->createTable('tmenus');
        $this->defineTMenusTable($tmenusTable);

        // User-Role relationship table
        $userRoleTable = $this->schema->createTable('user_role');
        $this->defineUserRoleTable($userRoleTable);

        // User-Permission relationship table
        $userPermissionTable = $this->schema->createTable('user_permission');
        $this->defineUserPermissionTable($userPermissionTable);

        // Menu-Role relationship table
        $menuRoleTable = $this->schema->createTable('menu_role');
        $this->defineMenuRoleTable($menuRoleTable);

        // Menu-SMenu relationship table
        $menuSMenuTable = $this->schema->createTable('menu_smenu');
        $this->defineMenuSMenuTable($menuSMenuTable);

        // SMenu-TMenu relationship table
        $tmenuSMenuTable = $this->schema->createTable('tmenu_smenu');
        $this->defineTMenuSMenuTable($tmenuSMenuTable);

        return $this->schema;
    }

    /**
     * Define the users table structure
     */
    protected function defineUsersTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('user_id', 'binary', ['length' => 12]);
        $table->addColumn('firstname', 'string', ['length' => 35]);
        $table->addColumn('lastname', 'string', ['length' => 35]);
        $table->addColumn('email', 'string', ['length' => 115]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('changepass', 'boolean', ['default' => false]);
        $table->addColumn('active', 'boolean', ['default' => true]);
        $table->addColumn('lastpasschanged', 'datetime', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 10]);
        $table->addColumn('admin', 'boolean', ['default' => false]);
        $table->addColumn('deleteuser', 'boolean', ['default' => false]);
        $table->addColumn('carrier', 'string', ['length' => 75, 'default' => '']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['user_id']);
        $table->addUniqueIndex(['id']);
        $table->addUniqueIndex(['email']);
        $table->addIndex(['firstname']);
        $table->addIndex(['lastname']);
        $table->addIndex(['active']);
        $table->addIndex(['phone']);
        $table->addIndex(['deleteuser']);
    }

    /**
     * Define the roles table structure
     */
    protected function defineRolesTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('role_id', 'binary', ['length' => 12]);
        $table->addColumn('name', 'string', ['length' => 25]);
        $table->addColumn('description', 'text', ['default' => '']);
        $table->addColumn('r_order', 'smallint', ['default' => 9, 'comment' => 'Role Order to show by, 0, 1, 2, 3, ..., 10, 15, 20, etc.']);
        $table->addColumn('role_weight', 'smallint', ['unsigned' => true, 'default' => 24, 'comment' => 'Role Weight to show by, 0, 1, 2, 3, ..., 10, 15, 20, etc. Max: 24']);
        $table->addColumn('active', 'boolean', ['default' => true, 'comment' => 'Ability to show or hide when necessary.']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['role_id']);
        $table->addUniqueIndex(['id']);
        $table->addUniqueIndex(['name']);
        $table->addIndex(['r_order']);
        $table->addIndex(['role_weight']);
        $table->addIndex(['active']);
    }

    /**
     * Define the permissions table structure
     */
    protected function definePermissionsTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('permission_id', 'binary', ['length' => 12]);
        $table->addColumn('name', 'string', ['length' => 25]);
        $table->addColumn('description', 'string', ['length' => 255]);
        $table->addColumn('bitwise', 'bigint', ['unsigned' => true, 'comment' => '1, 2, 4, 8, 16, 32, 64, 128, etc.']);
        $table->addColumn('p_order', 'smallint', ['default' => 24, 'comment' => '0, 1, 2, 3, ... 5, 10, 15, 20, etc. Max: 24']);
        $table->addColumn('active', 'boolean', ['default' => true, 'comment' => 'True: Active. False: Inactive.']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['permission_id']);
        $table->addUniqueIndex(['id']);
        $table->addIndex(['name']);
        $table->addIndex(['bitwise']);
        $table->addIndex(['p_order']);
        $table->addIndex(['active']);
    }

    /**
     * Define the menus table structure
     */
    protected function defineMenusTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('menu_id', 'binary', ['length' => 12]);
        $table->addColumn('name', 'string', ['length' => 20]);
        $table->addColumn('url', 'string', ['length' => 255]);
        $table->addColumn('controller', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('middleware', 'text', ['default' => '']);
        $table->addColumn('m_left', 'boolean', ['default' => true, 'comment' => 'True: Left. False: Right.']);
        $table->addColumn('noclick', 'boolean', ['default' => false, 'comment' => 'True: DO NOT ALLOW clicking on the URL. False: Allow clicking on the URL.']);
        $table->addColumn('bitwise', 'bigint', ['unsigned' => true, 'comment' => '1, 2, 4, 8, 16, 32, 64, 128, etc.']);
        $table->addColumn('m_order', 'smallint', ['default' => 24, 'comment' => '0, 1, 2, 3, ... 5, 10, 15, 20, etc. Max: 24']);
        $table->addColumn('visible', 'boolean', ['default' => true, 'comment' => 'True: Visible. False: Hidden.']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['menu_id']);
        $table->addUniqueIndex(['id']);
        $table->addIndex(['name']);
        $table->addIndex(['url']);
        $table->addIndex(['controller']);
        $table->addIndex(['m_left']);
        $table->addIndex(['m_order']);
        $table->addIndex(['bitwise']);
        $table->addIndex(['visible']);
    }

    /**
     * Define the smenus table structure
     */
    protected function defineSMenusTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('smenu_id', 'binary', ['length' => 12]);
        $table->addColumn('name', 'string', ['length' => 20]);
        $table->addColumn('url', 'string', ['length' => 255]);
        $table->addColumn('controller', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('middleware', 'text', ['default' => '']);
        $table->addColumn('noclick', 'boolean', ['default' => false, 'comment' => 'True: DO NOT ALLOW clicking on the URL. False: Allow clicking on the URL.']);
        $table->addColumn('bitwise', 'bigint', ['unsigned' => true, 'comment' => '1, 2, 4, 8, 16, 32, 64, 128, etc.']);
        $table->addColumn('s_order', 'smallint', ['default' => 24, 'comment' => '0, 1, 2, 3, ... 5, 10, 15, 20, etc. MAX: 24']);
        $table->addColumn('visible', 'boolean', ['default' => true, 'comment' => 'True: Visible. False: Hidden.']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['smenu_id']);
        $table->addUniqueIndex(['id']);
        $table->addIndex(['name']);
        $table->addIndex(['url']);
        $table->addIndex(['controller']);
        $table->addIndex(['bitwise']);
        $table->addIndex(['s_order']);
        $table->addIndex(['visible']);
    }

    /**
     * Define the tmenus table structure
     */
    protected function defineTMenusTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('tmenu_id', 'binary', ['length' => 12]);
        $table->addColumn('name', 'string', ['length' => 20]);
        $table->addColumn('url', 'string', ['length' => 255]);
        $table->addColumn('controller', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('middleware', 'text', ['default' => '']);
        $table->addColumn('noclick', 'boolean', ['default' => false, 'comment' => 'True: DO NOT ALLOW clicking on the URL. False: Allow clicking on the URL.']);
        $table->addColumn('bitwise', 'bigint', ['unsigned' => true, 'comment' => '1, 2, 4, 8, 16, 32, 64, 128, etc.']);
        $table->addColumn('t_order', 'smallint', ['default' => 24, 'comment' => '0, 1, 2, 3, ... 5, 10, 15, 20, etc. MAX: 24']);
        $table->addColumn('visible', 'boolean', ['default' => true, 'comment' => 'True: Visible. False: Hidden.']);
        $table->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['default' => '1900-01-01 00:00:00']);

        $table->setPrimaryKey(['tmenu_id']);
        $table->addUniqueIndex(['id']);
        $table->addIndex(['name']);
        $table->addIndex(['url']);
        $table->addIndex(['controller']);
        $table->addIndex(['bitwise']);
        $table->addIndex(['t_order']);
        $table->addIndex(['visible']);
    }

    /**
     * Define the user_role table structure
     */
    protected function defineUserRoleTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('user_id', 'binary', ['length' => 12, 'default' => '']);
        $table->addColumn('role_id', 'binary', ['length' => 12, 'default' => '']);

        $table->setPrimaryKey(['user_id', 'role_id']);
        $table->addUniqueIndex(['id']);

        // Add foreign keys
        $table->addForeignKeyConstraint('users', ['user_id'], ['user_id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('roles', ['role_id'], ['role_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the user_permission table structure
     */
    protected function defineUserPermissionTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12, 'unsigned' => true]);
        $table->addColumn('user_id', 'binary', ['length' => 12]);
        $table->addColumn('permission_id', 'binary', ['length' => 12]);

        $table->setPrimaryKey(['user_id', 'permission_id']);
        $table->addUniqueIndex(['id']);

        // Add foreign keys
        $table->addForeignKeyConstraint('users', ['user_id'], ['user_id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('permissions', ['permission_id'], ['permission_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the menu_role table structure
     */
    protected function defineMenuRoleTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('menu_id', 'binary', ['length' => 12, 'default' => '']);
        $table->addColumn('role_id', 'binary', ['length' => 12, 'default' => '']);

        $table->setPrimaryKey(['menu_id', 'role_id']);
        $table->addUniqueIndex(['id']);

        // Add foreign keys
        $table->addForeignKeyConstraint('menus', ['menu_id'], ['menu_id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('roles', ['role_id'], ['role_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the menu_smenu table structure
     */
    protected function defineMenuSMenuTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('menu_id', 'binary', ['length' => 12, 'default' => '']);
        $table->addColumn('smenu_id', 'binary', ['length' => 12, 'default' => '']);

        $table->setPrimaryKey(['menu_id', 'smenu_id']);
        $table->addUniqueIndex(['id']);

        // Add foreign keys
        $table->addForeignKeyConstraint('menus', ['menu_id'], ['menu_id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('smenus', ['smenu_id'], ['smenu_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the tmenu_smenu table structure
     */
    protected function defineTMenuSMenuTable(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'length' => 12]);
        $table->addColumn('tmenu_id', 'binary', ['length' => 12, 'default' => '']);
        $table->addColumn('smenu_id', 'binary', ['length' => 12, 'default' => '']);

        $table->setPrimaryKey(['tmenu_id', 'smenu_id']);
        $table->addUniqueIndex(['id']);

        // Add foreign keys
        $table->addForeignKeyConstraint('tmenus', ['tmenu_id'], ['tmenu_id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('smenus', ['smenu_id'], ['smenu_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Create the schema in the database
     * Only runs from the command line on the client side
     * 
     * @throws Exception
     */
    public function create(): void
    {
        // Ensure this only runs from the command line
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('This operation can only be performed from the command line.');
        }

        // Define the schema
        $this->define();

        // Generate SQL queries
        $queries = $this->schema->toSql($this->connection->getDatabasePlatform());

        // Execute each query
        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }
    }

    /**
     * Drop the schema from the database
     * Only runs from the command line on the client side
     * 
     * @throws Exception
     */
    public function drop(): void
    {
        // Ensure this only runs from the command line
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('This operation can only be performed from the command line.');
        }

        // Define the schema
        $this->define();

        // Generate SQL drop queries
        $queries = $this->schema->toDropSql($this->connection->getDatabasePlatform());

        // Execute each query
        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }
    }
}
