<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Console\Command\MigrateDatabase;
use JDS\Console\ConsoleRuntimeException;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\GenerateNewId;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class DatabaseConsoleServiceProvider implements ServiceProviderInterface
{
    private array $provides = [
        'database:migrations:migrate',
        MigrateDatabase::class
    ];

    public function __construct(private Container $container)
    {
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {

        //
        // 1. Ensure database connection exists
        //
        if (!$this->container->has(Connection::class)) {
            throw new ConsoleRuntimeException("Database Console Provider requires that a Doctrine DBAL Conneciton" .
            "be registered. Ensure Database Service Provider is loaded first.");
        }

        //
        // 2. Ensure New ID Generation exists
        //
        if (!$this->container->has(GenerateNewId::class)) {
            throw new ConsoleRuntimeException("Database Console Provider requires that the Generate NewId ServiceProvider" .
            "be registered. Ensure Database Service Provider is loaded first.");
        }

//        if (!$this->container->has(ErrorProcessor::class)) {
//            $this->container->addServiceProvider(new LoggingServiceProvider());
//        }

        $connection = $this->container->get(Connection::class);
        $config = $this->container->get('config');

        //
        // 3. Register migration command
        //    Migration command: database:migrations:migrate
        //
//        $migrateInit = [
//            'path'      => $config->get('initializePath'),
//            'database'  => $dbCfg['dbname'],
//            'user'      => $dbCfg['user'],
//            'password'  => $dbCfg['password'],
//        ];

        $this->container->add('database:migrations:migrate', MigrateDatabase::class)
            ->addArguments([
                $connection,
                new StringArgument($config->get('migrationsPath')),
                new StringArgument($config->get('migrateInit')),
                GenerateNewId::class
            ]);
    }
}

