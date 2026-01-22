<?php

namespace JDS\Security;


use JDS\Configuration\Config;
use JDS\Console\Commands\PurgeExpiredTokens;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Contracts\Security\TokenManagerInterface;
use JDS\Contracts\Security\TokenStoreInterface;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;
use RuntimeException;

class TokenServiceProvider implements ServiceProviderInterface
{
    protected $provides = [
        TokenManagerInterface::class,
        TokenManager::class
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(Container $container): void
    {
        if ($container->has(Config::class)) {
            throw new RuntimeException(
                'Configuration Service is not in the container. [Token:Service:Provider[.'
            );
        }

        // Secrets must be available to get jwtSecretkey



        $container->add(TokenManager::class)
            ->addArgument(new StringArgument($secret));

        // Bind interface to implementation
        $container->add(TokenManagerInterface::class, TokenManager::class)
            ->addArgument(new StringArgument($secret));

        $container->add(PurgeExpiredTokens::class)
            ->addArgument(TokenStoreInterface::class);

        $container->extend('console', function ($console, $c) {
            $console->add($c->get(PurgeExpiredTokens::class));
            return $console;
        });
    }
}


