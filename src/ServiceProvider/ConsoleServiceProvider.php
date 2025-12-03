<?php

namespace JDS\ServiceProvider;

use JDS\Console\Application;
use JDS\Console\Kernel;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * Registers the Console Kernel, Application, and base config values.
 */
class ConsoleServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
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
        $container = $this->getContainer();

        //
        // 1. Provide base namespace for command discovery
        //
        $container->add(
            'base-commands-namespace',
            new StringArgument('JDS\\Console\\Command\\')
        );

        //
        // 2. Provide array of user-defined commands
        //
        if (!$container->has('user-commands')) {
            $container->add('user-commands',[]);
        }

        //
        // 3. Register the Console Application
        //
        $container->add(Application::class)
            ->addArgument($container);

        //
        // 4. Register the Console Kernel
        //
        $container->add(Kernel::class)
            ->addArguments([
                $container,
                Application::class,
            ]);
    }
}

