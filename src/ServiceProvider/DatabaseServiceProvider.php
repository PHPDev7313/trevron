<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Auditor\Handlers\DatabaseLogHandler;
use JDS\Auditor\Provider\LogLevelProvider;
use JDS\Auditor\Validators\DatabaseLogJsonValidator;
use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Dbal\GenerateNewId;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function __construct(private readonly Container $container)
    {
    }

    protected array $provides = [
        ConnectionFactory::class,
        Connection::class,
        GenerateNewId::class,
        'database',
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {

        $config = $this->container->get(Config::class);
        //
        // 1. ConnectionFactory
        //
        $this->container->add(ConnectionFactory::class)
            ->addArgument($config->get('db'));

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
                new StringArgument($config->get('db_logger')),
                LogLevelProvider::class,
                DatabaseLogJsonValidator::class,
            ]);


//        $container->add(PurgeExpiredTokenCommand::class);
    }
}

