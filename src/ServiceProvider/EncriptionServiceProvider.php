<?php

namespace JDS\ServiceProvider;

use JDS\ServiceProvider\Encryption\Decryptor;
use JDS\ServiceProvider\Encryption\Encryptor;
use JDS\ServiceProvider\Encryption\KeyProvider;
use JDS\ServiceProvider\Encryption\NonceProvider;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class EncriptionServiceProvider implements ServiceProviderInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        // pull from config added by the application before bootstrap runs
        $config = $this->container->get('config');
        $key = $config->get('crypt') ?? 'fallback-secret-2025-secret-fallback';

        // Add KeyProvider
        $this->container->add(KeyProvider::class)
            ->addArgument(new StringArgument($key));

        // Add NonceProvider
        $this->container->add(NonceProvider::class);

        // Add Encryptor
        $this->container->add(Encryptor::class)
            ->addArguments([KeyProvider::class, NonceProvider::class]);

        // Add Decryptor
        $this->container->add(Decryptor::class)
            ->addArgument( KeyProvider::class);
    }
}

