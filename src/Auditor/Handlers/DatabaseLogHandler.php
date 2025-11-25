<?php

namespace JDS\Auditor\Handlers;

use Doctrine\DBAL\Connection;
use JDS\Auditor\Exception\InvalidArgumentException;
use JDS\Auditor\Provider\LogLevelProvider;
use JDS\Auditor\Validators\DatabaseLogJsonValidator;
use JDS\Contracts\Auditor\LogHandlerInterface;
use JDS\Dbal\AbstractDatabaseHelper;
use JDS\Dbal\GenerateNewId;
use JDS\Processing\ErrorProcessor;
use Throwable;

class DatabaseLogHandler extends AbstractDatabaseHelper implements LogHandlerInterface
{
    public function __construct(
        private Connection $connection,
        private string $tableName,
        private GenerateNewId $newId,
        private LogLevelProvider $logLevelProvider,
        private DatabaseLogJsonValidator $jsonValidator
    )
    {
        $this->ensureTableExists();
    }

    public function handle(array $logEntry): void
    {
        if (!isset($logEntry['level'], $logEntry['message'])) {
            $exitCode = 218;
            ErrorProcessor::process(
                new InvalidArgumentException('Log entry must contain "level" and "message".'),
                $exitCode,
                'Log entry must contain "level" and "message".'
            );
            exit($exitCode);

        }
        if (!is_string($logEntry['message'])) {
            $exitCode = 217;
            ErrorProcessor::process(
                new InvalidArgumentException('Log entry "message" must be a string.'),
                $exitCode,
                'Log entry "message" must be a string.'
            );
            exit($exitCode);
        }
        // validate level
        if (!in_array($logEntry['level'], $this->logLevelProvider->getValidLogLevels(), true)) {
            $exitCode = 213;
            ErrorProcessor::process(
                new InvalidArgumentException('Invalid log level.'),
                $exitCode,
                'Invalid log level.'
            );
            exit($exitCode);
        }

        try {
            $this->writeLogToDatabase($logEntry['level'], $logEntry['message'], $logEntry['context'] ?? [], );

        } catch (Throwable $e) {
            $exitCode = 205;
            ErrorProcessor::process($e, $exitCode, 'Failed to log entry.');
            exit($exitCode);
        }
    }

    public function readLog(
        ?string $level = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 100): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from($this->tableName)
            ->setMaxResults($limit)
            ->orderBy('created', 'DESC');

        if (!is_null($level)) {
            $queryBuilder->andWhere('level = :level')
            ->setParameter('level', $level);
        }

        if (!is_null($startDate)) {
            $queryBuilder->andWhere('created >= :startDate')
            ->setParameter('startDate', $startDate);
        }

        if (!is_null($endDate)) {
            $queryBuilder->andWhere('created <= :endDate')
            ->setParameter('endDate', $endDate);
        }
        return $queryBuilder->fetchAllAssociative();
    }

    private function ensureTableExists(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist([$this->tableName])) {
            $sql = sprintf("CREATE TABLE IF NOT EXISTS %s (
            id INT(12) UNSIGNED AUTO_INCREMENT,
            log_id varbinary(12) NOT NULL,
            level VARCHAR(10) NOT NULL COMMENT 'Severity level (e.g., \"INFO\", \"ERROR\")',
            message TEXT NOT NULL COMMENT 'The main log message',
            context JSON DEFAULT NULL COMMENT 'Additional context as JSON', 
            created DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of the log',
            PRIMARY KEY (log_id),
            UNIQUE KEY id (id),
            KEY level (level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ", $this->tableName);
            $this->connection->executeStatement($sql);
        }
    }

    /**
     * Writes a log entry to the database with the specified log details.
     *
     * @param string $level The severity level of the log entry (e.g., error, info, debug).
     * @param string $message The log message to be stored.
     * @param array $context Additional contextual data associated with the log entry.
     *
     * @return void
     */
    private function writeLogToDatabase(string $level, string $message, array $context): void
    {

        $validatedContext = $this->jsonValidator->validateAndEncode($context);

        $logId = $this->newId->getNewId();
        // prepare SQL statement
        $sql = sprintf("INSERT INTO %s (log_id, level, message, context) VALUES (:logId, :level, :message, :context); ", $this->tableName);

        // prepare and bind parameters
        $stmt = $this->connection->prepare($sql);
        $this->bind($stmt, "logId", $logId);
        $this->bind($stmt, "level", $level);
        $this->bind($stmt, "message", $message);
        $this->bind($stmt, "context", $validatedContext);
        $stmt->executeStatement();

    }
}
