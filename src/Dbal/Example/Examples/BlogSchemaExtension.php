<?php

namespace JDS\Dbal\Example\Examples;

use Doctrine\DBAL\Schema\Schema;
use JDS\Dbal\Example\AbstractSchemaExtension;

/**
 * Example schema extension that adds blog-related tables
 */
class BlogSchemaExtension extends AbstractSchemaExtension
{
    /**
     * {@inheritdoc}
     */
    protected function extendSchema(Schema $schema): void
    {
        // Create posts table
        $postsTable = $this->createTableIfNotExists($schema, 'blog_posts');
        $this->definePostsTable($postsTable);

        // Create categories table
        $categoriesTable = $this->createTableIfNotExists($schema, 'blog_categories');
        $this->defineCategoriesTable($categoriesTable);

        // Create post_categories table (many-to-many relationship)
        $postCategoriesTable = $this->createTableIfNotExists($schema, 'blog_post_categories');
        $this->definePostCategoriesTable($postCategoriesTable);

        // Create comments table
        $commentsTable = $this->createTableIfNotExists($schema, 'blog_comments');
        $this->defineCommentsTable($commentsTable);
    }

    /**
     * Define the blog_posts table structure
     */
    private function definePostsTable($table): void
    {
        $this->addColumnIfNotExists($table, 'id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'user_id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'title', 'string', ['length' => 255]);
        $this->addColumnIfNotExists($table, 'slug', 'string', ['length' => 255]);
        $this->addColumnIfNotExists($table, 'content', 'text');
        $this->addColumnIfNotExists($table, 'excerpt', 'text', ['notnull' => false]);
        $this->addColumnIfNotExists($table, 'status', 'string', ['length' => 20, 'default' => 'draft']);
        $this->addColumnIfNotExists($table, 'created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $this->addColumnIfNotExists($table, 'updated', 'datetime', ['default' => '1900-01-01 00:00:00']);
        $this->addColumnIfNotExists($table, 'published', 'datetime', ['notnull' => false]);
        
        // Set primary key if not already set
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey(['id']);
        }
        
        // Add unique index for slug if not already added
        if (!$table->hasIndex('blog_posts_slug_idx')) {
            $table->addUniqueIndex(['slug'], 'blog_posts_slug_idx');
        }
        
        // Add foreign key to users table if not already added
        $this->addForeignKeyIfNotExists($table, 'users', ['user_id'], ['user_id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the blog_categories table structure
     */
    private function defineCategoriesTable($table): void
    {
        $this->addColumnIfNotExists($table, 'id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'name', 'string', ['length' => 255]);
        $this->addColumnIfNotExists($table, 'slug', 'string', ['length' => 255]);
        $this->addColumnIfNotExists($table, 'description', 'text', ['notnull' => false]);
        $this->addColumnIfNotExists($table, 'created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $this->addColumnIfNotExists($table, 'updated', 'datetime', ['default' => '1900-01-01 00:00:00']);
        
        // Set primary key if not already set
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey(['id']);
        }
        
        // Add unique index for slug if not already added
        if (!$table->hasIndex('blog_categories_slug_idx')) {
            $table->addUniqueIndex(['slug'], 'blog_categories_slug_idx');
        }
    }

    /**
     * Define the blog_post_categories table structure
     */
    private function definePostCategoriesTable($table): void
    {
        $this->addColumnIfNotExists($table, 'post_id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'category_id', 'binary', ['length' => 12]);
        
        // Set primary key if not already set
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey(['post_id', 'category_id']);
        }
        
        // Add foreign keys if not already added
        $this->addForeignKeyIfNotExists($table, 'blog_posts', ['post_id'], ['id'], ['onDelete' => 'CASCADE']);
        $this->addForeignKeyIfNotExists($table, 'blog_categories', ['category_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    /**
     * Define the blog_comments table structure
     */
    private function defineCommentsTable($table): void
    {
        $this->addColumnIfNotExists($table, 'id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'post_id', 'binary', ['length' => 12]);
        $this->addColumnIfNotExists($table, 'user_id', 'binary', ['length' => 12, 'notnull' => false]);
        $this->addColumnIfNotExists($table, 'parent_id', 'binary', ['length' => 12, 'notnull' => false]);
        $this->addColumnIfNotExists($table, 'author_name', 'string', ['length' => 255, 'notnull' => false]);
        $this->addColumnIfNotExists($table, 'author_email', 'string', ['length' => 255, 'notnull' => false]);
        $this->addColumnIfNotExists($table, 'content', 'text');
        $this->addColumnIfNotExists($table, 'status', 'string', ['length' => 20, 'default' => 'pending']);
        $this->addColumnIfNotExists($table, 'created', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $this->addColumnIfNotExists($table, 'updated', 'datetime', ['default' => '1900-01-01 00:00:00']);
        
        // Set primary key if not already set
        if (!$table->hasPrimaryKey()) {
            $table->setPrimaryKey(['id']);
        }
        
        // Add foreign keys if not already added
        $this->addForeignKeyIfNotExists($table, 'blog_posts', ['post_id'], ['id'], ['onDelete' => 'CASCADE']);
        $this->addForeignKeyIfNotExists($table, 'users', ['user_id'], ['user_id'], ['onDelete' => 'SET NULL']);
        $this->addForeignKeyIfNotExists($table, 'blog_comments', ['parent_id'], ['id'], ['onDelete' => 'SET NULL']);
    }
}