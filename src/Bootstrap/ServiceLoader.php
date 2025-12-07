<?php

namespace JDS\Bootstrap;

use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\ServiceProvider\ServiceProviderRuntimeException;
use League\Container\Container;

class ServiceLoader
{
    private Container $container;
    private array $providers = [];

    /**
     * @param array<string, mixed> $configData
     */
    public function __construct(array $configData)
    {
        //
        // Create DI container
        //
        $this->container = new Container();

        //
        // Bind the Config service immediately as a shared service.
        // This is the *foundation* of the framework.
        //
        $config = new Config($configData);

        $this->container->addShared(Config::class, $config);
    }

    public function addProvider(string $providerClass): self
    {
        $this->providers[] = $providerClass;
        return $this;
    }

    /**
     * Load all providers and return a fully-constructed container.
     */
    public function boot(): Container
    {
        foreach ($this->providers as $providerClass) {
            $this->loadProvider($providerClass);
        }

        return $this->container;
    }

    private function loadProvider(string $providerClass): void
    {
        try {
            //
            // Ensure class exist
            //
            if (!class_exists($providerClass)) {
                throw new ServiceProviderRuntimeException(
                    "Provider class does not exist: {$providerClass}. [Service:Provider]."
                );
            }

            //
            // Instantiate provider (must be no-arg constructor)
            $provider = new $providerClass();

            //
            // Ensure it implements correct interface
            //
            if (!$provider instanceof ServiceProviderInterface) {
                throw new ServiceProviderRuntimeException(
                    "Provider must implement Service Provider Interface: {$providerClass}. [Service:Provider]."
                );
            }

            //
            // IMPORTANT
            // Provider gets ONLY the container.
            // No raw config, no data, no manipulation.
            //
            $provider->register($this->container);

        } catch (\Throwable $e) {
            throw new ServiceProviderRuntimeException(
                "Failed to load provider: {$providerClass}. Error: {$e->getMessage()}. [Service:Provider].",
                previous: $e
            );
        }
    }
}

