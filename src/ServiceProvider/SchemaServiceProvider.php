<?php

namespace JDS\ServiceProvider;

use JDS\Container\Container;
use JDS\Container\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Dbal\DataMapper;
use JDS\Dbal\Schema\SchemaManager;

class SchemaServiceProvider implements ServiceProviderInterface
{
    /**
     * Register services in the container
     */
    public function register(Container $container): void
    {
        // Register the SchemaManager
        $container->set(SchemaManager::class, function (Container $container) {
            return new SchemaManager(
                $container->get(ConnectionFactory::class)->create(),
                $container->get(DataMapper::class)
            );
        });
    }

    /**
     * Boot the service provider
     */
    public function boot(Container $container): void
    {
        // Nothing to do here
    }
}