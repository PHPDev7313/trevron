<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Console\Command\MigrateDatabase;
use JDS\Console\ConsoleRuntimeException;
use JDS\Dbal\GenerateNewId;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;

class DatabaseConsoleServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    private array $provides = [
        'database:migrations:migrate',
        MigrateDatabase::class
    ];

    public function provides(string $id): bool
    {
        return array_key_exists($id, $this->provides, true);
    }

    public function register(): void
    {
        $container = $this->getContainer();

        //
        // 1. Ensure database connection exists
        //
        if (!$container->has(Connection::class)) {
            throw new ConsoleRuntimeException("Database Console Provider requires that a Doctrine DBAL Conneciton" .
            "be registered. Ensure Database Service Provider is loaded first.");
        }

        $connection = $container->get(Connection::class);
        $config = $container->get('config');

        //
        // 2. Register migration command
        //
        $container->add('database:migrations:migrate', MigrateDatabase::class)
            ->addArguments([
                $connection,
                new StringArgument($config->get('migrationsPath')),
                new StringArgument($config->get('migateInit')),
                GenerateNewId::class
            ]);
    }
}

