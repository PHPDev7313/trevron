<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Platforms;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Platforms\Keywords\PostgreSQLKeywords;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Name\UnquotedIdentifierFolding;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\DBAL\Types\Types;
use Doctrine\Deprecations\Deprecation;
use UnexpectedValueException;

use function array_merge;
use function array_unique;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function strtolower;
use function substr;
use function trim;

/**
 * Provides the behavior, features and SQL dialect of the PostgreSQL database platform
 * of the oldest supported version.
 */
class PostgreSQLPlatform extends AbstractPlatform
{
    private bool $useBooleanTrueFalseStrings = true;

    /** @var string[][] PostgreSQL booleans literals */
    private array $booleanLiterals = [
        'true' => [
            't',
            'true',
            'y',
            'yes',
            'on',
            '1',
        ],
        'false' => [
            'f',
            'false',
            'n',
            'no',
            'off',
            '0',
        ],
    ];

    public function __construct()
    {
        parent::__construct(UnquotedIdentifierFolding::LOWER);
    }

    /**
     * PostgreSQL has different behavior with some drivers
     * with regard to how booleans have to be handled.
     *
     * Enables use of 'true'/'false' or otherwise 1 and 0 instead.
     */
    public function setUseBooleanTrueFalseStrings(bool $flag): void
    {
        $this->useBooleanTrueFalseStrings = $flag;
    }

    public function getRegexpExpression(): string
    {
        return 'SIMILAR TO';
    }

    public function getLocateExpression(string $string, string $substring, ?string $start = null): string
    {
        if ($start !== null) {
            $string = $this->getSubstringExpression($string, $start);

            return 'CASE WHEN (POSITION(' . $substring . ' IN ' . $string . ') = 0) THEN 0'
                . ' ELSE (POSITION(' . $substring . ' IN ' . $string . ') + ' . $start . ' - 1) END';
        }

        return sprintf('POSITION(%s IN %s)', $substring, $string);
    }

    protected function getDateArithmeticIntervalExpression(
        string $date,
        string $operator,
        string $interval,
        DateIntervalUnit $unit,
    ): string {
        if ($unit === DateIntervalUnit::QUARTER) {
            $interval = $this->multiplyInterval($interval, 3);
            $unit     = DateIntervalUnit::MONTH;
        }

        return '(' . $date . ' ' . $operator . ' (' . $interval . " || ' " . $unit->value . "')::interval)";
    }

    public function getDateDiffExpression(string $date1, string $date2): string
    {
        return '(DATE(' . $date1 . ')-DATE(' . $date2 . '))';
    }

    public function getCurrentDatabaseExpression(): string
    {
        return 'CURRENT_DATABASE()';
    }

    public function supportsSequences(): bool
    {
        return true;
    }

    public function supportsSchemas(): bool
    {
        return true;
    }

    public function supportsIdentityColumns(): bool
    {
        return true;
    }

    /** @internal The method should be only used from within the {@see AbstractPlatform} class hierarchy. */
    public function supportsPartialIndexes(): bool
    {
        return true;
    }

    /** @internal The method should be only used from within the {@see AbstractPlatform} class hierarchy. */
    public function supportsCommentOnStatement(): bool
    {
        return true;
    }

    /** @internal The method should be only used from within the {@see AbstractSchemaManager} class hierarchy. */
    public function getListDatabasesSQL(): string
    {
        return 'SELECT datname FROM pg_database';
    }

    /** @internal The method should be only used from within the {@see AbstractSchemaManager} class hierarchy. */
    public function getListSequencesSQL(string $database): string
    {
        return 'SELECT sequence_name AS relname,
                       sequence_schema AS schemaname,
                       minimum_value AS min_value,
                       increment AS increment_by
                FROM   information_schema.sequences
                WHERE  sequence_catalog = ' . $this->quoteStringLiteral($database) . "
                AND    sequence_schema NOT LIKE 'pg\_%'
                AND    sequence_schema != 'information_schema'";
    }

    /** @internal The method should be only used from within the {@see AbstractSchemaManager} class hierarchy. */
    public function getListViewsSQL(string $database): string
    {
        return 'SELECT quote_ident(table_name) AS viewname,
                       table_schema AS schemaname,
                       view_definition AS definition
                FROM   information_schema.views
                WHERE  view_definition IS NOT NULL';
    }

    /** @internal The method should be only used from within the {@see AbstractPlatform} class hierarchy. */
    public function getAdvancedForeignKeyOptionsSQL(ForeignKeyConstraint $foreignKey): string
    {
        $query = '';

        if ($foreignKey->hasOption('match')) {
            $query .= ' MATCH ' . $foreignKey->getOption('match');
        }

        $query .= parent::getAdvancedForeignKeyOptionsSQL($foreignKey);

        $deferrabilitySQL = $this->getConstraintDeferrabilitySQL($foreignKey);

        if ($deferrabilitySQL !== '') {
            $query .= $deferrabilitySQL;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlterTableSQL(TableDiff $diff): array
    {
        $sql         = [];
        $commentsSQL = [];

        $table = $diff->getOldTable();

        $tableNameSQL = $table->getQuotedName($this);

        foreach ($diff->getAddedColumns() as $addedColumn) {
            $query = 'ADD ' . $this->getColumnDeclarationSQL(
                $addedColumn->getQuotedName($this),
                $addedColumn->toArray(),
            );

            $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ' . $query;

            $comment = $addedColumn->getComment();

            if ($comment === '') {
                continue;
            }

            $commentsSQL[] = $this->getCommentOnColumnSQL(
                $tableNameSQL,
                $addedColumn->getQuotedName($this),
                $comment,
            );
        }

        foreach ($diff->getDroppedColumns() as $droppedColumn) {
            $query = 'DROP ' . $droppedColumn->getQuotedName($this);
            $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ' . $query;
        }

        foreach ($diff->getChangedColumns() as $columnDiff) {
            $oldColumn = $columnDiff->getOldColumn();
            $newColumn = $columnDiff->getNewColumn();

            $oldColumnName = $oldColumn->getQuotedName($this);
            $newColumnName = $newColumn->getQuotedName($this);

            if ($columnDiff->hasNameChanged()) {
                $sql = array_merge(
                    $sql,
                    $this->getRenameColumnSQL($tableNameSQL, $oldColumnName, $newColumnName),
                );
            }

            $newTypeSQLDeclaration = $this->getTypeSQLDeclaration($newColumn);
            $oldTypeSQLDeclaration = $this->getTypeSQLDeclaration($oldColumn);
            if ($oldTypeSQLDeclaration !== $newTypeSQLDeclaration) {
                $query = 'ALTER ' . $newColumnName . ' TYPE ' . $newTypeSQLDeclaration;
                $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ' . $query;
            }

            if ($columnDiff->hasDefaultChanged()) {
                $defaultClause = $newColumn->getDefault() === null
                    ? ' DROP DEFAULT'
                    : ' SET' . $this->getDefaultValueDeclarationSQL($newColumn->toArray());

                $query = 'ALTER ' . $newColumnName . $defaultClause;
                $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ' . $query;
            }

            if ($columnDiff->hasNotNullChanged()) {
                $query = 'ALTER ' . $newColumnName . ' ' . ($newColumn->getNotnull() ? 'SET' : 'DROP') . ' NOT NULL';
                $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ' . $query;
            }

            if ($columnDiff->hasAutoIncrementChanged()) {
                if ($newColumn->getAutoincrement()) {
                    $query = 'ADD GENERATED BY DEFAULT AS IDENTITY';
                } else {
                    $query = 'DROP IDENTITY';
                }

                $sql[] = 'ALTER TABLE ' . $tableNameSQL . ' ALTER ' . $newColumnName . ' ' . $query;
            }

            if (! $columnDiff->hasCommentChanged()) {
                continue;
            }

            $commentsSQL[] = $this->getCommentOnColumnSQL(
                $tableNameSQL,
                $newColumn->getQuotedName($this),
                $newColumn->getComment(),
            );
        }

        return array_merge(
            $this->getPreAlterTableIndexForeignKeySQL($diff),
            $sql,
            $commentsSQL,
            $this->getPostAlterTableIndexForeignKeySQL($diff),
        );
    }

    private function getTypeSQLDeclaration(Column $column): string
    {
        $type = $column->getType();

        // SERIAL/BIGSERIAL are not "real" types and we can't alter a column to that type
        $columnDefinition                  = $column->toArray();
        $columnDefinition['autoincrement'] = false;

        return $type->getSQLDeclaration($columnDefinition, $this);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRenameIndexSQL(string $oldIndexName, Index $index, string $tableName): array
    {
        if (str_contains($tableName, '.')) {
            [$schema]     = explode('.', $tableName);
            $oldIndexName = $schema . '.' . $oldIndexName;
        }

        return ['ALTER INDEX ' . $oldIndexName . ' RENAME TO ' . $index->getQuotedName($this)];
    }

    public function getCreateSequenceSQL(Sequence $sequence): string
    {
        return 'CREATE SEQUENCE ' . $sequence->getQuotedName($this) .
            ' INCREMENT BY ' . $sequence->getAllocationSize() .
            ' MINVALUE ' . $sequence->getInitialValue() .
            ' START ' . $sequence->getInitialValue() .
            $this->getSequenceCacheSQL($sequence);
    }

    public function getAlterSequenceSQL(Sequence $sequence): string
    {
        return 'ALTER SEQUENCE ' . $sequence->getQuotedName($this) .
            ' INCREMENT BY ' . $sequence->getAllocationSize() .
            $this->getSequenceCacheSQL($sequence);
    }

    /**
     * Cache definition for sequences
     */
    private function getSequenceCacheSQL(Sequence $sequence): string
    {
        if ($sequence->getCache() > 1) {
            return ' CACHE ' . $sequence->getCache();
        }

        return '';
    }

    public function getDropSequenceSQL(string $name): string
    {
        return parent::getDropSequenceSQL($name) . ' CASCADE';
    }

    public function getDropForeignKeySQL(string $foreignKey, string $table): string
    {
        return $this->getDropConstraintSQL($foreignKey, $table);
    }

    public function getDropIndexSQL(string $name, string $table): string
    {
        if (str_ends_with($table, '"')) {
            $primaryKeyName = substr($table, 0, -1) . '_pkey"';
        } else {
            $primaryKeyName = $table . '_pkey';
        }

        if ($name === '"primary"' || $name === $primaryKeyName) {
            Deprecation::triggerIfCalledFromOutside(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/6867',
                'Building the SQL for dropping primary key constraint via %s() is deprecated. Use'
                    . ' getDropConstraintSQL() instead.',
                __METHOD__,
            );

            return $this->getDropConstraintSQL($primaryKeyName, $table);
        }

        if (str_contains($table, '.')) {
            [$schema] = explode('.', $table);
            $name     = $schema . '.' . $name;
        }

        return parent::getDropIndexSQL($name, $table);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCreateTableSQL(string $name, array $columns, array $options = []): array
    {
        $this->validateCreateTableOptions($options, __METHOD__);

        $queryFields = $this->getColumnDeclarationListSQL($columns);

        if (! empty($options['primary'])) {
            $keyColumns   = array_unique(array_values($options['primary']));
            $queryFields .= ', PRIMARY KEY (' . implode(', ', $keyColumns) . ')';
        }

        $unlogged = isset($options['unlogged']) && $options['unlogged'] === true ? ' UNLOGGED' : '';

        $query = 'CREATE' . $unlogged . ' TABLE ' . $name . ' (' . $queryFields . ')';

        $sql = [$query];

        if (! empty($options['indexes'])) {
            foreach ($options['indexes'] as $index) {
                $sql[] = $this->getCreateIndexSQL($index, $name);
            }
        }

        if (isset($options['uniqueConstraints'])) {
            foreach ($options['uniqueConstraints'] as $uniqueConstraint) {
                $sql[] = $this->getCreateUniqueConstraintSQL($uniqueConstraint, $name);
            }
        }

        if (isset($options['foreignKeys'])) {
            foreach ($options['foreignKeys'] as $definition) {
                $sql[] = $this->getCreateForeignKeySQL($definition, $name);
            }
        }

        return $sql;
    }

    /**
     * Converts a single boolean value.
     *
     * First converts the value to its native PHP boolean type
     * and passes it to the given callback function to be reconverted
     * into any custom representation.
     *
     * @param mixed    $value    The value to convert.
     * @param callable $callback The callback function to use for converting the real boolean value.
     *
     * @throws UnexpectedValueException
     */
    private function convertSingleBooleanValue(mixed $value, callable $callback): mixed
    {
        if ($value === null) {
            return $callback(null);
        }

        if (is_bool($value) || is_numeric($value)) {
            return $callback((bool) $value);
        }

        if (! is_string($value)) {
            return $callback(true);
        }

        /**
         * Better safe than sorry: http://php.net/in_array#106319
         */
        if (in_array(strtolower(trim($value)), $this->booleanLiterals['false'], true)) {
            return $callback(false);
        }

        if (in_array(strtolower(trim($value)), $this->booleanLiterals['true'], true)) {
            return $callback(true);
        }

        throw new UnexpectedValueException(sprintf(
            'Unrecognized boolean literal, %s given.',
            $value,
        ));
    }

    /**
     * Converts one or multiple boolean values.
     *
     * First converts the value(s) to their native PHP boolean type
     * and passes them to the given callback function to be reconverted
     * into any custom representation.
     *
     * @param mixed    $item     The value(s) to convert.
     * @param callable $callback The callback function to use for converting the real boolean value(s).
     */
    private function doConvertBooleans(mixed $item, callable $callback): mixed
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                $item[$key] = $this->convertSingleBooleanValue($value, $callback);
            }

            return $item;
        }

        return $this->convertSingleBooleanValue($item, $callback);
    }

    /**
     * {@inheritDoc}
     *
     * Postgres wants boolean values converted to the strings 'true'/'false'.
     */
    public function convertBooleans(mixed $item): mixed
    {
        if (! $this->useBooleanTrueFalseStrings) {
            return parent::convertBooleans($item);
        }

        return $this->doConvertBooleans(
            $item,
            /** @param mixed $value */
            static function ($value): string {
                if ($value === null) {
                    return 'NULL';
                }

                return $value === true ? 'true' : 'false';
            },
        );
    }

    public function convertBooleansToDatabaseValue(mixed $item): mixed
    {
        if (! $this->useBooleanTrueFalseStrings) {
            return parent::convertBooleansToDatabaseValue($item);
        }

        return $this->doConvertBooleans(
            $item,
            /** @param mixed $value */
            static function ($value): ?int {
                return $value === null ? null : (int) $value;
            },
        );
    }

    /**
     * @param T $item
     *
     * @return (T is null ? null : bool)
     *
     * @template T
     */
    public function convertFromBoolean(mixed $item): ?bool
    {
        if (in_array($item, $this->booleanLiterals['false'], true)) {
            return false;
        }

        return parent::convertFromBoolean($item);
    }

    public function getSequenceNextValSQL(string $sequence): string
    {
        return "SELECT NEXTVAL('" . $sequence . "')";
    }

    public function getSetTransactionIsolationSQL(TransactionIsolationLevel $level): string
    {
        return 'SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL '
            . $this->_getTransactionIsolationLevelSQL($level);
    }

    /**
     * {@inheritDoc}
     */
    public function getBooleanTypeDeclarationSQL(array $column): string
    {
        return 'BOOLEAN';
    }

    /**
     * {@inheritDoc}
     */
    public function getIntegerTypeDeclarationSQL(array $column): string
    {
        return 'INT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getBigIntTypeDeclarationSQL(array $column): string
    {
        return 'BIGINT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $column): string
    {
        return 'SMALLINT' . $this->_getCommonIntegerTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getGuidTypeDeclarationSQL(array $column): string
    {
        return 'UUID';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $column): string
    {
        return 'TIMESTAMP(0) WITHOUT TIME ZONE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeTzTypeDeclarationSQL(array $column): string
    {
        return 'TIMESTAMP(0) WITH TIME ZONE';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTypeDeclarationSQL(array $column): string
    {
        return 'DATE';
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $column): string
    {
        return 'TIME(0) WITHOUT TIME ZONE';
    }

    /**
     * {@inheritDoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $column): string
    {
        if (! empty($column['autoincrement'])) {
            return ' GENERATED BY DEFAULT AS IDENTITY';
        }

        return '';
    }

    protected function getVarcharTypeDeclarationSQLSnippet(?int $length): string
    {
        $sql = 'VARCHAR';

        if ($length !== null) {
            $sql .= sprintf('(%d)', $length);
        }

        return $sql;
    }

    protected function getBinaryTypeDeclarationSQLSnippet(?int $length): string
    {
        return 'BYTEA';
    }

    protected function getVarbinaryTypeDeclarationSQLSnippet(?int $length): string
    {
        return 'BYTEA';
    }

    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $column): string
    {
        return 'TEXT';
    }

    public function getDateTimeTzFormatString(): string
    {
        return 'Y-m-d H:i:sO';
    }

    public function getEmptyIdentityInsertSQL(string $quotedTableName, string $quotedIdentifierColumnName): string
    {
        return 'INSERT INTO ' . $quotedTableName . ' (' . $quotedIdentifierColumnName . ') VALUES (DEFAULT)';
    }

    public function getTruncateTableSQL(string $tableName, bool $cascade = false): string
    {
        $tableIdentifier = new Identifier($tableName);
        $sql             = 'TRUNCATE ' . $tableIdentifier->getQuotedName($this);

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }

    /**
     * Get the snippet used to retrieve the default value for a given column
     */
    public function getDefaultColumnValueSQLSnippet(): string
    {
        return <<<'SQL'
             SELECT pg_get_expr(adbin, adrelid)
             FROM pg_attrdef
             WHERE c.oid = pg_attrdef.adrelid
                AND pg_attrdef.adnum=a.attnum
        SQL;
    }

    protected function initializeDoctrineTypeMappings(): void
    {
        $this->doctrineTypeMapping = [
            'bigint'           => Types::BIGINT,
            'bigserial'        => Types::BIGINT,
            'bool'             => Types::BOOLEAN,
            'boolean'          => Types::BOOLEAN,
            'bpchar'           => Types::STRING,
            'bytea'            => Types::BLOB,
            'char'             => Types::STRING,
            'date'             => Types::DATE_MUTABLE,
            'decimal'          => Types::DECIMAL,
            'double precision' => Types::FLOAT,
            'float'            => Types::FLOAT,
            'float4'           => Types::SMALLFLOAT,
            'float8'           => Types::FLOAT,
            'inet'             => Types::STRING,
            'int'              => Types::INTEGER,
            'int2'             => Types::SMALLINT,
            'int4'             => Types::INTEGER,
            'int8'             => Types::BIGINT,
            'integer'          => Types::INTEGER,
            'interval'         => Types::STRING,
            'json'             => Types::JSON,
            'jsonb'            => Types::JSON,
            'money'            => Types::DECIMAL,
            'numeric'          => Types::DECIMAL,
            'serial'           => Types::INTEGER,
            'serial4'          => Types::INTEGER,
            'serial8'          => Types::BIGINT,
            'real'             => Types::SMALLFLOAT,
            'smallint'         => Types::SMALLINT,
            'text'             => Types::TEXT,
            'time'             => Types::TIME_MUTABLE,
            'timestamp'        => Types::DATETIME_MUTABLE,
            'timestamptz'      => Types::DATETIMETZ_MUTABLE,
            'timetz'           => Types::TIME_MUTABLE,
            'tsvector'         => Types::TEXT,
            'uuid'             => Types::GUID,
            'varchar'          => Types::STRING,
            '_varchar'         => Types::STRING,
        ];
    }

    /** @deprecated */
    protected function createReservedKeywordsList(): KeywordList
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/6607',
            '%s is deprecated.',
            __METHOD__,
        );

        return new PostgreSQLKeywords();
    }

    /**
     * {@inheritDoc}
     */
    public function getBlobTypeDeclarationSQL(array $column): string
    {
        return 'BYTEA';
    }

    /**
     * {@inheritDoc}
     *
     * @internal The method should be only used from within the {@see AbstractPlatform} class hierarchy.
     */
    public function getDefaultValueDeclarationSQL(array $column): string
    {
        if (isset($column['autoincrement']) && $column['autoincrement'] === true) {
            return '';
        }

        return parent::getDefaultValueDeclarationSQL($column);
    }

    /** @internal The method should be only used from within the {@see AbstractPlatform} class hierarchy. */
    public function supportsColumnCollation(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getJsonTypeDeclarationSQL(array $column): string
    {
        if (! empty($column['jsonb'])) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/6939',
                'The "jsonb" column platform option is deprecated. Use the "JSONB" type instead.',
            );

            return 'JSONB';
        }

        return 'JSON';
    }

    /**
     * {@inheritDoc}
     */
    public function getJsonbTypeDeclarationSQL(array $column): string
    {
        return 'JSONB';
    }

    public function createSchemaManager(Connection $connection): PostgreSQLSchemaManager
    {
        return new PostgreSQLSchemaManager($connection, $this);
    }
}
