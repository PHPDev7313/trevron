<?php

namespace JDS\ServiceProvider;

use Doctrine\DBAL\Connection;
use JDS\Configuration\Config;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Exceptions\Database\DatabaseRuntimeException;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;

final class DatabaseConnectionServiceProvider implements ServiceProviderInterface
{
    private const REQUIRED_CONFIG_KEYS = [
        'driver',
        'host',
        'port',
        'dbname',
    ];

    private const REQUIRED_SECRET_KEYS = [
        'db.user',
        'db.password',
    ];

    protected array $provides = [
        ConnectionFactory::class,
        Connection::class,
    ];

    public function register(Container $container): void
    {
        // --------------------------------------------------
        // 1. Config MUST exist
        // --------------------------------------------------
        if (!$container->has(Config::class)) {
            throw new DatabaseRuntimeException(
                "Config must be loaded before database connection provider. [Database:Connection:Service:Provider]."
            );
        }

        /** @var Config $config */
        $config = $container->get(Config::class);

        $dbConfig = $config->get('db');

        if (!is_array($dbConfig)) {
            throw new DatabaseRuntimeException(
                "Database configuration missing or invalid. Expected array at config key 'db'. [Database:Connection:Service:Provider]."
            );
        }

        $this->validateBaseConfig($dbConfig);

        // --------------------------------------------------
        // 2. Secrets MUST exist AND be locked
        // --------------------------------------------------
        if (!$container->has(LockableSecretsInterface::class)) {
            throw new DatabaseRuntimeException(
                "Secrets must be registered before database provider. [Database:Connection:Service:Provider]."
            );
        }

        /** @var LockableSecretsInterface $secrets */
        $secrets = $container->get(LockableSecretsInterface::class);

        if (!$secrets->isLocked()) {
            throw new DatabaseRuntimeException(
                "Secrets must be locked before database connection can be created. [Database:Connection:Service:Provider]."
            );
        }

        $this->validateRequiredSecrets($secrets);

        // --------------------------------------------------
        // 3. Overlay credentials from secrets (NO fallback)
        // --------------------------------------------------
        $dbConfig['user']     = $secrets->get('db.user');
        $dbConfig['password'] = $secrets->get('db.password');

        // --------------------------------------------------
        // 4. Register ConnectionFactory
        // --------------------------------------------------
        $container->add(ConnectionFactory::class)
            ->addArgument(new ArrayArgument($dbConfig))
            ->setShared(true);

        // --------------------------------------------------
        // 5. Register lazy shared Connection
        // --------------------------------------------------
        $container->addShared(Connection::class, function () use ($container) {
            return $container->get(ConnectionFactory::class)->create();
        });
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    private function validateBaseConfig(array $config): void
    {
        $missing = array_diff(self::REQUIRED_CONFIG_KEYS, array_keys($config));

        if (!empty($missing)) {
            throw new DatabaseRuntimeException(
                "Missing required database config keys: " . implode(', ', $missing) .
                ". [Database:Connection:Service:Provider]."
            );
        }
    }

    private function validateRequiredSecrets(LockableSecretsInterface $secrets): void
    {
        foreach (self::REQUIRED_SECRET_KEYS as $key) {
            if (!$secrets->has($key)) {
                throw new DatabaseRuntimeException(
                    "Missing required database secret '{$key}'. [Database:Connection:Service:Provider]."
                );
            }
        }
    }
}


