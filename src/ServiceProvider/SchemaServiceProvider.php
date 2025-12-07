<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Dbal\ConnectionFactory;
use JDS\Dbal\DataMapper;
use JDS\Dbal\Example\SchemaManager;
use League\Container\Container;

class SchemaServiceProvider implements ServiceProviderInterface
{
    /**
     * Register services in the container
     */
    public function register(Container $container): void
    {
        // Register the SchemaManager
        $container->add(SchemaManager::class, function (Container $container) {
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

