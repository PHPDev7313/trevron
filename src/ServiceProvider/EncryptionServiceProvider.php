<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use JDS\Crypt\Crypto;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use JDS\ServiceProvider\EncriptionServiceProvider as LegacyEncryptProvider;

class EncryptionServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        'crypto-generator',
        LegacyEncryptProvider::class,
    ];
    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $config = $this->container->get(Config::class);

        //
        // 1. Crypto generator
        //
        $this->container->add('crypto-generator', Crypto::class)
            ->addArgument(
                new StringArgument($config->get('encryptionKey'))
            );

        //
        // 2. Existing EncriptionServiceProvider wrapper
        //
        // NOTE:
        // Because your legacy provider requires the container as an argument,
        // we keep it here for backward compatibility.
        //
        $this->container->add(LegacyEncryptProvider::class)
            ->addArgument($this->container);
    }
}

