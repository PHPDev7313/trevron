<?php

namespace JDS\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use JDS\Authentication\RuntimeException;
use JDS\Console\ConsoleException;
use JDS\Console\DatabaseNotFoundException;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Dbal\GenerateNewId;
use JDS\Http\FileNotFoundException;
use JDS\Http\FileWriteException;
use JDS\Processing\ErrorProcessor;
use JsonException;
use PDOException;
use Throwable;

class MigrateDatabase implements CommandInterface
{
    public string $name = 'database:migrations:migrate';

    public function __construct(
        private Connection     $connection,
        private string         $migrationsPath,
        private readonly array $initialize,
        private GenerateNewId  $newId
    )
    {
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function execute(array $params = []): int
    {

        try {
            $this->handleMigrationDirection($params);
        } catch (ConsoleException $e) {
            // handle or log the exception
            $exitCode = 16;
            ErrorProcessor::process(
                $e,
                $exitCode,
                'Invalid Direction Parameter.'
            );
            exit($exitCode);
        }

        $execute = 0;
        // migrations up
        // create a migrations table SQL if table not already in existence

        $this->initializeProject($this->initialize);
        if (array_key_exists('up', $params)) {
            if (is_numeric($params['up'])) {
                try {
                    $up = $params['up'];
                    $found = false;
                    $migrationFiles = $this->getMigrationFiles();
                    if (empty($migrationFiles)) {
                        throw new ConsoleException('There are no migrations to apply...');
                    }
                    foreach ($migrationFiles as $migration) {
                        $mig_number = (int)substr($migration, 1, strpos($migration, '_') - 1);
                        if ($mig_number == $up) {
                            $this->executeMigration('up', $migration, $this->getConnection());
                            $this->insertMigration($migration);
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        echo 'Migration ' . $up . ' successfully applied!' . PHP_EOL;
                    } else {
                        echo 'Migration ' . $up . ' not found! No Migrations were applied...' . PHP_EOL;
                    }
                } catch (Throwable $e) {
                    $exitCode = 9;
                    ErrorProcessor::process(
                        $e,
                        $exitCode,
                        sprintf("Error applying migration: %s", $up)
                    );
                    exit($exitCode);
                 }
            } else {
                $migrationCalled = '';
                try {
                    // get $appliedMigrations which are already in the database.migrations table
                    // since we are also going for the down as well as the up
                    // we'll add a flag to be able to order in the proper order
                    $appliedMigrations = $this->getAppliedMigrations();

                    // get the $migrationFiles from the migrations folder
                    $migrationFiles = $this->getMigrationFiles();

                    // get the migrations to apply. i.e. they are in $migrationFiles but not in
                    // $appliedMigrations
                    $migrationsToApply = array_diff($migrationFiles, $appliedMigrations);

                    // create SQL for any migrations which have not been run ... i.e. which are not in the
                    // database

                    if (count($migrationsToApply) > 0) {
                        // loop through migrations in ascending order

                        foreach ($migrationsToApply as $migration) {
                        $migrationCalled = $migration;
                            // call the up method
                            $up = false;
                            if (!empty($params['up'])) {
                                $up = true;
                                $upCalled = true;
                                $this->executeMigration('up', $migration);
                                // add migration to database
                                $this->insertMigration($migration);
                                echo "Migration '{$migration}' applied successfully." . PHP_EOL;
                            }
                        }
                    } else {
                        echo "All migrations have been applied..." . PHP_EOL;
                    }
                    unset($migrationCalled);
                } catch (ConsoleException $e) {
                    // catch any fatal/unhandled exceptions and log or display the error message
                    $exitCode = 9;
                    ErrorProcessor::process($e,
                        $exitCode,
                        sprintf("An error occurred while applying migration: %s", $migrationCalled)
                    );
                    exit($exitCode);
                }
            }
            // migrations down
        } elseif (array_key_exists('down', $params)) {
            try {
                if (is_numeric($params['down'])) {
                    $down = $params['down'];
                    $found = false;
                    $migrationFiles = $this->getMigrationFiles();
                    if (count($migrationFiles) > 0) {
                        foreach ($migrationFiles as $migration) {
                            $mig_number = (int)substr($migration, 1, strpos($migration, '_') - 1);
                            if ($mig_number == $down) {
                                $this->executeMigration('down', $migration, $this->getConnection());
                                $found = true;
                                break;
                            }
                        }
                    } else {
                        echo "There are no migrations to roll back..." . PHP_EOL;
                    }
                    if ($found) {
                        echo 'Migration ' . $down . ' successfully rolled back!' . PHP_EOL;
                    } else {
                        echo 'Migration ' . $down . ' not found! No Migrations were rolled back...' . PHP_EOL;
                    }
                } else {
                    // get migrations applied
                    $appliedMigrations = $this->getAppliedMigrations();
                    // loop through migrations in descending order
                    $mig_count = 0;
                    if (count($appliedMigrations) > 0) {
                        foreach (array_reverse($appliedMigrations, true) as $migration) {
                            if (file_exists($this->migrationsPath . '/' . $migration)) {
                                // call the down method
                                $this->executeMigration('down', $migration, $this->getConnection());
                                // remove the migration from database
                                $this->removeMigration($migration);
                                $mig_count++;
                            } else {
                                $this->removeMigration($migration);
                                echo 'Migration file ' . $migration . ' not found! Removing from migrations' . PHP_EOL;
                            }
                        }
                    } else {
                        echo "There are no migrations to roll back..." . PHP_EOL;
                    }
                    if ($mig_count >= count($appliedMigrations)) {
                        $this->connection->executeQuery('TRUNCATE TABLE migrations;');
                    }
                }
            } catch (Throwable $e) {
                $exitCode = 10;
                ErrorProcessor::process(
                    $e,
                    $exitCode,
                    sprintf("Error rolling back migration: %s", implode(', ', array_reverse($this->getAppliedMigrations())))
                );
                exit($exitCode);
            }
        }
        return 0;
    }

    private function handleMigrationDirection(array $params): void
    {
        if (array_key_exists('up', $params)) {
            $this->printMigrationMessage($params['up'], 'Up');
        } elseif (array_key_exists('down', $params)) {
            $this->printMigrationMessage($params['down'], 'Down');
        } else {
            throw new ConsoleException(sprintf("Invalid parameters. Please use: --up or --down! Can also be --up=(integer) or --down=(integer) to specify the migration number to run. Example: --up-1 would run m00001_name.php and --down-1 would run m00001_name.php."));
        }
    }

    private function printMigrationMessage(int $number, string $direction): void
    {
        $msg = sprintf("Executing: %s \"%s\" ", $this->name, $direction);
        if (is_numeric($number)) {
            $msg .= sprintf("for migration number %d", $number);
        }
        echo $msg . PHP_EOL;
    }

    /**
     * @throws Exception
     */
    private function insertMigration($migration): void
    {
        $sql = "INSERT INTO 
                    migrations 
                    (
                        migration_id, 
                        migration
                    ) 
                VALUES 
                    (
                        :migrationid, 
                        :mg
                     ); ";
        try {
            $stmt = $this->connection->prepare($sql);

            $stmt->bindValue(':mg', $migration);
            $stmt->bindValue(':migrationid', $this->newId->getNewId());

            $stmt->executeStatement();
        } catch (Throwable $e) {
            $exitCode = 11;
            ErrorProcessor::process(
                $e,
                $exitCode,
                sprintf("Error inserting migration: %s", $migration)
            );
            exit($exitCode);
        }
    }

    /**
     * @throws Exception
     */
    private function removeMigration($migration): void
    {
        $sql = "DELETE FROM 
                    migrations 
                WHERE 
                    migration = :mg;";
        try {
            $stmt = $this->connection->prepare($sql);

            $stmt->bindValue(':mg', $migration);

            $stmt->executeStatement();
        } catch (Throwable $e) {
            $exitCode = 11;
            ErrorProcessor::process(
                $e,
                $exitCode,
                sprintf("Error removing migration: %s", $migration)
            );
            exit($exitCode);
        }
    }

    private function getMigrationFiles(): array
    {
        try {
            $migrationFiles = scandir($this->migrationsPath);
            $filterdFiles = array_filter($migrationFiles, function ($file) {
                return !in_array($file, ['.', '..', '.gitignore', 'm00000_template.php']);
            });
            return $filterdFiles;
        } catch (Throwable $e) {
            $exitCode = 12;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "Error retrieving migration files"
            );
            exit($exitCode);
        }
    }


    /**
     * @throws Throwable
     * @throws Exception
     */
    public function initializeProject(array $initialize): void
    {
        try {


            $filePath = $initialize['path'] . '/initialized.json'; // Path to the JSON file

            $this->createMigrationsTable();

            // Step 1: Check if the file exists and the `initialized` flag is set
            if ($this->isProjectInitialized($filePath)) {
                echo 'Project is already initialized. Skipping setup.' . PHP_EOL;
                return;
            }

            // Step 2: Run the initialization process
            if (!$this->checkIfDatabaseExists($initialize['database'])) {
                $this->createDatabase($initialize['database']);
                $this->createUser($initialize['user'], $initialize['pass']);
            }


            // Step 3: Mark the project as initialized in the JSON file
            $this->markProjectAsInitialized($filePath);

            echo 'Project setup complete.' . PHP_EOL;
        } catch (JsonException $e) {
            $exitCode = 25;
            ErrorProcessor::process(
                $e,
                $exitCode,
                "Error: Invalid JSON format in file: {$filePath}.");
            exit($exitCode);
        } catch (FileNotFoundException $e) {
            // handle a specific file-related error
            $exitCode = 26;
            ErrorProcessor::process($e, $exitCode, "Error: File not found: {$filePath}.");
            exit($exitCode);
        } catch (DatabaseNotFoundException $e) {
            // handle a database-related exception
            $exitCode = 30;
            ErrorProcessor::process($e, $exitCode, "A database-related error occurred.");
            exit($exitCode);
        } catch (Exception $e) {
            $exitCode = 1;
            ErrorProcessor::process($e, $exitCode, "An unexpected error occurred. Contact support.");
            exit($exitCode);
        }
    }


    private function isProjectInitialized(string $filePath): bool
    {
        try {
            // Check if the initialization file exists
            if (!file_exists($filePath)) {
                return false; // File doesn't exist, assume not initialized
            }
            // safely read file content
            // suppresses PHP warnings for controlled error handling
            $content = @file_get_contents($filePath);

            // verify if `file_get_content` returned valid content
            if ($content === false) {
                throw new FileNotFoundException("Failed to read the file at: {$filePath}.");
            }

            // decode the JSON file and handle errors
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \JsonException("Invalid JSON format in file: {$filePath}.");
            }

            // Check if the `initialized` key is set and true
            return (isset($data['initialized']) && $data['initialized'] === true);
        } catch (JsonException $e) {
            $exitCode = 25;
            ErrorProcessor::process($e, $exitCode, "Error: Invalid JSON format in initialization file.");
            return false;
        } catch (FileNotFoundException $e) {
            $exitCode = 26;
            ErrorProcessor::process($e, $exitCode, "Error: Initialization File not found.");
            return false;
        } catch (Throwable $e) {
            $exitCode = 29;
            ErrorProcessor::process($e, $exitCode, "Error: An unexpected error occurred. Contact support.");
            return false;
        }
    }

    private function markProjectAsInitialized(string $filePath): void
    {
        // Step 1: Build the JSON data
        $data = [
            'initialized' => true,
            'timestamp' => date('c') // Current timestamp in ISO 8601 format
        ];

        // Step 2: Write to the file
        try {
            @file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        } catch (FileWriteException $e) {
            $exitCode = 40;
            ErrorProcessor::process($e, $exitCode, "Failed to write initilation data");
            exit($exitCode);
        }
    }



    /**
     * Retrieves the list of applied migrations from the database.
     *
     * @return array The list of applied migrations.
     * @throws Exception
     */
    private function getAppliedMigrations(): array
    {
        $sql = "SELECT 
                    migration 
                FROM 
                    migrations 
                ORDER BY 
                    migration; ";
        try {
            return $this->connection->executeQuery($sql)->fetchFirstColumn();
        } catch (Throwable $e) {
            $exitCode = 17;
            ErrorProcessor::process($e, $exitCode, sprintf("Error retrieving applied migrations: %s", $e->getMessage()));
            exit($exitCode);
        }
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    private function createMigrationsTable(): void
    {
        // schema manager
        $schemaManager = $this->connection->createSchemaManager();

        // if tables does NOT exist, create it
        if (!$schemaManager->tablesExist(['migrations'])) {
            // schema
            $schema = new Schema();
            try {

                // create table
                $table = $schema->createTable('migrations')->addOption('engine', 'InnoDB');

                // id
                $table->addColumn('id', Types::INTEGER, ['length' => 12, 'unsigned' => true, 'autoincrement' =>
                    true]);

                $table->addColumn('migration_id', Types::STRING, ['length' => 12]);

                // migration name
                $table->addColumn('migration', Types::STRING, ['length' => 60]);

                // datetime
                $table->addColumn('created_at', Types::DATETIME_IMMUTABLE, ['default' => 'CURRENT_TIMESTAMP']);

                // primary key
                $table->setPrimaryKey(['id']);

                $sqlArray = $schema->toSql($this->connection->getDatabasePlatform());
                if (count($sqlArray) > 0) {
                    $this->connection->executeQuery($sqlArray[0]);
                    echo 'migrations table created' . PHP_EOL;
                }
            } catch (Throwable $throwable) {
                $exitCode = 13;
                ErrorProcessor::process($throwable, $exitCode, sprintf("Error creating migrations table. Message: %s", $throwable->getMessage()));
                exit($exitCode);
            }
        } else {
            echo '<< migrations table already exists >>' . PHP_EOL . 'Create Migrations Table Skipped!' . PHP_EOL;
        }

    }

    /**
     * @throws ConsoleException
     */
    private function executeMigration(string $direction, string $migration): void
    {
        $migrationObject = require $this->migrationsPath . '/' . $migration;

        try {
            $migrationObject->$direction($migration, $this->getConnection());
        } catch (PDOException $pe) {
            $this->handlePDOException($pe, $migration);
            exit(9);
        } catch (Throwable $e) {
            $exitCode = 18;
            ErrorProcessor::process($e, $exitCode, "An unexpected error occurred during migration");
            exit($exitCode);
        }
    }

    private function handlePDOException(PDOException $pe, string $migration): void
    {
        // get the specific SQL error code or null if unavailable
        $errorCode = $pe->errorInfo[1] ?? null;
        // SQL error code or null if unavailable
        $sqlState = $pe->errorInfo[0] ?? 'UNKNOWN';
        // map database error codes to specific error messages
        $errorMessages = [
            1062 => "Duplicate entry for migration '%s'.",
            1451 => "Cannot delete or update a parent row: a foreign key constraint fails for migration '%s'.",
            1049 => "Unknown database for migration '%s'.",
            1045 => "Access denied for user during migration '%s'."
        ];
        // create a simple message for production with the error code

        $detailedMessage = $errorMessages[$errorCode] ?? "Database error during migration.";

        // build the error message
        $errorDetails = sprintf($detailedMessage . " SQLSTATE[%s]: %s %s",
            $sqlState,
            $errorCode,
        $pe->errorInfo[2] ?? 'No additional information available.',
        );
        // return a new exception with the formatted message and SQLSTATE information
        $messageToDisplay = $errorDetails;
        // log the detailed error message
        $exitCode = 19;
        ErrorProcessor::process($pe, $exitCode, $messageToDisplay);
        exit($exitCode);
    }

    private function getConnection(): Connection
    {
        if (is_null($this->connection)) {
            throw new RuntimeException("No database connection is available.");
        }
        return $this->connection;
    }

    /**
     * @throws Exception
     */
    private function checkIfDatabaseExists(string $database): bool
    {
        $sql = "SELECT 
                    count(*) 
                FROM 
                    INFORMATION_SCHEMA.TABLES 
                WHERE 
                    TABLE_SCHEMA = :databaseName;";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('databaseName', $database);
            $result = $stmt->executeQuery()->fetchFirstColumn();
            return ((int)$result > 0);
        } catch (Throwable $e) {
            $exitCode = 33;
            ErrorProcessor::process($e, $exitCode, sprintf("An error occurred while checking if the database \"%s\" exists,", $database));
            exit($exitCode);
         }
    }

    /**
     * @throws Exception
     */
    private function createDatabase(string $database): void
    {
        $sql = "CREATE DATABASE IF NOT EXISTS $database DEFAULT CHARACTER SET utf8mb4; ";
        try {
            $this->connection->executeQuery($sql);
        } catch (Throwable $e) {
            // log and handle the exception
            $exitCode = 31;
            ErrorProcessor::process($e, $exitCode, "An error occurred while creating the database");
            exit($exitCode);
        }
    }

    /**
     * @throws Exception
     */
    private function createUser(string $username, string $password): void
    {
        $sql = "CREATE USER IF NOT EXISTS '$username'@'localhost' IDENTIFIED BY '$password'; ";
        try {
            $this->connection->executeQuery($sql);
        } catch (Throwable $e) {
            // log and handle exception
            $exitCode = 32;
            ErrorProcessor::process($e, $exitCode, "An error occurred while creating the database user.");
            exit($exitCode);
        }
    }
}
