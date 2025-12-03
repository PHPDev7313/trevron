<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array<string>
     */
    protected array $provides = [
        'config',
        Config::class
    ];

    /**
     * Accept the already prepared config data array.
     */
    public function __construct(private readonly array $configData, private readonly Container $container)
    {
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        // Bind the Config data
        $this->container->add('config-data', new ArrayArgument($this->configData));

        // Bind the Config object
        $this->container->add('config', Config::class)
            ->addArgument('config-data');

        // Allow resolving by class-name as well
        $this->container->add(Config::class)
            ->addArgument('config-data');
    }
}

