<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Console\ConsoleRuntimeException;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use League\Container\Container;

class DatabaseConnectionServiceProvider implements ServiceProviderInterface
{
    /**
     * The services this provider can supply
     */
    protected array $provides = [
        ConnectionFactory::class,
        Connection::class
    ];

    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        //
        // fetch config from container
        //
        $config = $this->container->get('config');
        $dbConfig = $config->get('db');

        if (!isset($dbConfig['driver'])) {
            throw new ConsoleRuntimeException("Missing 'driver' key in config");
        }

        if (!isset($dbConfig['host'])) {
            throw new ConsoleRuntimeException("Missing 'host' key in config");
        }

        if (!isset($dbConfig['user'])) {
            throw new ConsoleRuntimeException("Missing 'user' key in config");
        }

        if (!isset($dbConfig['password'])) {
            throw new ConsoleRuntimeException("Missing 'pass' key in config");
        }

        if (!isset($dbConfig['dbname'])) {
            throw new ConsoleRuntimeException("Missing 'dbname' key in config");
        }

        if (!isset($dbConfig['port'])) {
            throw new ConsoleRuntimeException("Missing 'port' key in config");
        }

        if (!is_array($dbConfig)) {
            throw new ConsoleRuntimeException("Database configuration is missing or invalid. Expected array at 'db'.");
        }

        //
        // register factory
        //
        $this->container->add(ConnectionFactory::class)
            ->addArgument($dbConfig);

        //
        // register chared Doctrine connection
        //
        $this->container->addShared(Connection::class, function () {
            return $this->container->get(ConnectionFactory::class)->create();
        });
   }

    /**
     * Framework / League requirement.
     */
    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }
}

