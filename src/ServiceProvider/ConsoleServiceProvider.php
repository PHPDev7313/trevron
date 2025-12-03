<?php

namespace JDS\ServiceProvider;

use JDS\Console\Application;
use JDS\Console\Kernel;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

/**
 * Registers the Console Kernel, Application, and base config values.
 */
class ConsoleServiceProvider implements ServiceProviderInterface
{
    /**
     * Services this provider delivers
     */
    private array $provides = [
        Kernel::class,
        Application::class,
        'base-commands-namespace',
        'user-commands',
    ];

    public function __construct(private Container $container)
    {
    }

    /**
     * Whether this provider offers the given service id.
     */
    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    /**
     * Register console components.
     */
    public function register(): void
    {

        //
        // 1. Provide base namespace for command discovery
        //
        $this->container->add(
            'base-commands-namespace',
            new StringArgument('JDS\\Console\\Command\\')
        );

        //
        // 2. Provide array of user-defined commands
        //
        if (!$this->container->has('user-commands')) {
            $this->container->add('user-commands',[]);
        }

        //
        // 3. Register the Console Application
        //
        $this->container->add(Application::class)
            ->addArgument($this->container);

        //
        // 4. Register the Console Kernel
        //
        $this->container->add(Kernel::class)
            ->addArguments([
                $this->container,
                Application::class,
            ]);
    }
}

