<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Auditor\Handlers\DatabaseLogHandler;
use JDS\Auditor\Provider\LogLevelProvider;
use JDS\Auditor\Validators\DatabaseLogJsonValidator;
use JDS\Configuration\Config;
use JDS\Console\Command\MigrateDatabase;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Dbal\GenerateNewId;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        ConnectionFactory::class,
        Connection::class,
        GenerateNewId::class,
        'database',
        'database:migrations:migrate',
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $config = $this->container->get(Config::class);
        $dbCfg = $config->get('db');

        //
        // 1. ConnectionFactory
        //
        $this->container->add(ConnectionFactory::class)
            ->addArgument($dbCfg);

        //
        // 2. Shared Doctrine DBAL Connection
        //
        $this->container->addShared(Connection::class, function () {
            return $this->container->get(ConnectionFactory::class)->create();
        });

        //
        // 3. GenerateNewId
        //
        $this->container->add(GenerateNewId::class);

        //
        // 4. DatabaseLogHandler ('database')
        //
        $this->container->add('database', DatabaseLogHandler::class)
            ->addArguments([
                Connection::class,
                new StringArgument($config->get('db_audit_log')),
                GenerateNewId::class,
                LogLevelProvider::class,
                DatabaseLogJsonValidator::class,
            ]);

        //
        // 5. Migration command: database:migrations:migrate
        //
        $migrateInit = [
            'path'      => $config->get('initializePath'),
            'database'  => $dbCfg['dbname'],
            'user'      => $dbCfg['user'],
            'password'  => $dbCfg['password'],
        ];

        $this->container->add('database:migrations:migrate', MigrateDatabase::class)
            ->addArguments([
                Connection::class,
                new StringArgument($config->get('migrationsPath')),
                new ArrayArgument($migrateInit),
                GenerateNewId::class,
            ]);
    }
}

