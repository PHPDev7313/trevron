<?php

namespace JDS\Contracts\Dbal\Example;

use Doctrine\DBAL\Schema\Schema;

interface SchemaExtensionInterface
{
    /**
     * Extend the database schema with additional tables or modifications
     */
    public function extend(Schema $schema): void;
}