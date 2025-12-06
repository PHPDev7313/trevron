<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\SecretsInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\ServiceProvider\Encryption\Decryptor;
use JDS\ServiceProvider\Encryption\Encryptor;
use JDS\ServiceProvider\Encryption\KeyProvider;
use JDS\ServiceProvider\Encryption\NonceProvider;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class EncryptionServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        SecretsInterface::class,
        KeyProvider::class,
        NonceProvider::class,
        Encryptor::class,
        Decryptor::class
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

     public function register(Container $container): void
    {
        // pull from config added by the application before bootstrap runs
        $config = $container->get('config');
        $secrets = $container->get(SecretsInterface::class);

        // Add KeyProvider
        $container->add(KeyProvider::class)
            ->addArgument(new StringArgument($secrets->get('encryption.crypt')));

        // Add NonceProvider
        $container->add(NonceProvider::class);

        // Add Encryptor
        $container->add(Encryptor::class)
            ->addArguments([KeyProvider::class, NonceProvider::class]);

        // Add Decryptor
        $container->add(Decryptor::class)
            ->addArgument( KeyProvider::class);
    }
}

