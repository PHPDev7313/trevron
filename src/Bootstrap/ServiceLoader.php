<?php

namespace JDS\Bootstrap;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\ServiceProvider\ServiceProviderRuntimeException;
use League\Container\Container;

class ServiceLoader
{
    private array $providers = [];

    public function __construct(private Container $container)
    {
    }

    public function addProvider(string $provderClass): self
    {
        $this->providers[] = $provderClass;
        return $this;
    }

    public function loadAll(): void
    {
        foreach ($this->providers as $providerClass) {
            $this->loadProvider($providerClass);
        }
    }

    public function loadSelected(array $providerClassess): void
    {
        foreach ($providerClassess as $providerClass) {
            $this->loadProvider($providerClass);
        }
    }

    private function loadProvider(string $providerClass): void
    {
        try {
            $provider = new $providerClass($this->container);

            if (!$provider instanceof ServiceProviderInterface) {
                throw new ServiceProviderRuntimeException("Provider must implement ServiceProviderInterface: {$providerClass}. [Service:Loader].");
            }

            $provider->register($this->container);
        } catch (\Throwable $e) {
            throw new ServiceProviderRuntimeException(
                "Failed to load service provider: {$providerClass}. Error: {$e->getMessage()}. [Service:Loader].",
            0,
                $e
            );
        }
    }
}

