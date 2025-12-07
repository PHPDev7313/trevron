<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Configuration\Config;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Exceptions\Database\DatabaseRuntimeException;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;

class DatabaseConnectionServiceProvider implements ServiceProviderInterface
{
    private array $requiredKeys = ['driver', 'host', 'port', 'dbname'];

    /**
     * The services this provider can supply
     */
    protected array $provides = [
        ConnectionFactory::class,
        Connection::class
    ];

    public function register(Container $container): void
    {
        //
        // 1. Fetch base db config from container (driver, host, dbname, port)
        //
        if (!$container->has(Config::class)) {
            throw new DatabaseRuntimeException(
                "Config must be loaded before registering database connection. [Database:Connection:Service:Provider]."
            );
        }

        /** @var Config $config */
        $config = $container->get(Config::class);


        $dbConfig = $config->get('db');

        if (!is_array($dbConfig)) {
            throw new DatabaseRuntimeException(
                "Database configuration is missing or invalid. Expected an array. [Database:Connection:ServiceProvider]"
            );
        }

        //
        // 2. Validate non-sensitive DB keys (driver, host, port, dbname)
        //
        $this->validateDatabaseConfig($dbConfig);

        //
        // 3. Overlay credentials from encrypted secrets
        //
        if (!$container->has(SecretsInterface::class)) {
            throw new DatabaseRuntimeException(
                "Missing Secrets configuration binding. Secrets must be available before DB provider loads. [Database:Connection:Service:Provider]."
            );
        }

        /** @var SecretsInterface $secrets */
        $secrets = $container->get(SecretsInterface::class);

        $dbConfig['user'] = $secrets->get('db.user', $dbConfig['user'] ?? null);
        $dbConfig['password'] = $secrets->get('db.password', $dbConfig['password'] ?? null);

        if (!$dbConfig['user'] && !$dbConfig['password']) {
            throw new DatabaseRuntimeException(
                "Database credentials (user/password) missing: could not load from secrets. [Database:Connection:Service:Provider]."
            );
        }

        //
        // 4. Register the ConnectionFactory
        //
        $container->add(ConnectionFactory::class)
            ->addArgument(new ArrayArgument($dbConfig));


        //
        // 5. Register shared Doctrine connection (lazy)
        //
        if (!$container->has(Connection::class)) {
            $container->addShared(Connection::class, function () use ($container) {
                return $container->get(ConnectionFactory::class)->create();
            });
        }
   }

    /**
     * Framework / League requirement.
     */
    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    private function validateDatabaseConfig(array $config): void
    {
        $missingKeys = array_diff($this->requiredKeys, array_keys($config));

        if (!empty($missingKeys)) {
            throw new DatabaseRuntimeException(
                "Missing required database configuration keys (non-sensitive): " .
                implode(', ', $missingKeys) .
                ". [Database:Connection:Service:Provider]."
            );
        }
    }
}

