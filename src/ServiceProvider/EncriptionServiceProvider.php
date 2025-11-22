<?php

namespace JDS\ServiceProvider;

use JDS\ServiceProvider\Encryption\Decryptor;
use JDS\ServiceProvider\Encryption\Encryptor;
use JDS\ServiceProvider\Encryption\KeyProvider;
use JDS\ServiceProvider\Encryption\NonceProvider;
use JDS\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class EncriptionServiceProvider implements ServiceProviderInterface
{
    private $container=null;
    protected $providers = [
        KeyProvider::class,
        NonceProvider::class,
        Encryptor::class,
        Decryptor::class
    ];

    public function __construct(ContainerInterface $container)
    {
    }

    public function initContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        global $container;
        $config = $this->container->get('config');
        // resolve encryption key from env/config
        $this->container->add(KeyProvider::class, function ()  {
            $secret = $this->container->get('config')->get('crypt') ?: 'fallback-secret-2025-secret-fallback';
            return new KeyProvider($secret);
        });

        $this->container->add(NonceProvider::class, fn() => new NonceProvider());

        $this->container->add(Encryptor::class, function () {
            return new Encryptor(
                $this->container->get(KeyProvider::class),
                $this->container->get(NonceProvider::class)
            );
        });

        $this->container->add(Decryptor::class, function () {
            return new Decryptor(
                $this->container->get(KeyProvider::class),
            );
        });
    }
}