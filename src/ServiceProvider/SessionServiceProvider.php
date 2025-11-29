<?php

namespace JDS\ServiceProvider;

use JDS\Authentication\SessionAuthentication;
use JDS\Configuration\Config;
use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Session\Session;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class SessionServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array<string>
     */
    protected array $provides = [
        SessionInterface::class,
        SessionAuthentication::class,
        Session::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $config = $this->container->get(Config::class);

        //
        // 1. SessionInterface -> Session
        //
        $this->container->addShared(
            SessionInterface::class,
            Session::class
        )->addArgument(
            new StringArgument($config->get('prefix'))
        );

        //
        // 2. SessionAuthentication
        // this needs to be moved or the authentication
        // needs to be fixed
        //
        $this->container->add(SessionAuthentication::class)
            ->addArguments([
                UserRepository::class,
                $config->get('jwtSecretKey'),
                $config->get('jwtRefreshSecretKey'),
            ]);
    }
}

