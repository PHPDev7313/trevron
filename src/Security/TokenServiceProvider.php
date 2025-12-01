<?php

namespace JDS\Security;


use JDS\Configuration\Config;
use JDS\Console\Command\PurgeExpiredTokens;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Contracts\Security\TokenManagerInterface;
use JDS\Contracts\Security\TokenStoreInterface;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class TokenServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $provides = [
        TokenManagerInterface::class,
        TokenManager::class
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $container = $this->getContainer();

        /** @var Config $config */
        $config = $container->get('config');

        // You can change this key name if you prefer.
        $secret = $config->get('jwtSecretKey');

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