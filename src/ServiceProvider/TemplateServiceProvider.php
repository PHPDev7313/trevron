<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Http\Navigation\BreadcrumbGenerator;
use JDS\Http\Navigation\MenuGenerator;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class TemplateServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array<string>
     */
    protected array $provides = [
        MenuGenerator::class,
        BreadcrumbGenerator::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(Container $container): void
    {
        $config = $container->get(Config::class);

        //
        // 1. MenuGenerator
        //
        $container->add(MenuGenerator::class)
            ->addArguments([
                new StringArgument($config->get('basePath') . $config->get('menuPath')),
                new StringArgument($config->get('menuFile')),
            ]);

        //
        // 2. BreadcrumbGenerator
        //
        $container->add(BreadcrumbGenerator::class)
            ->addArguments([
                new ArrayArgument($config->get('routes')),
                new StringArgument($config->get('routePath')),
            ]);
    }
}

