<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use JDS\Http\Generators\BreadcrumbGenerator;
use JDS\Http\Generators\MenuGenerator;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class TemplateServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
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

    public function register(): void
    {
        $config = $this->container->get(Config::class);

        //
        // 1. MenuGenerator
        //
        $this->container->add(MenuGenerator::class)
            ->addArguments([
                new StringArgument($config->get('basePath') . $config->get('menuPath')),
                new StringArgument($config->get('menuFile')),
            ]);

        //
        // 2. BreadcrumbGenerator
        //
        $this->container->add(BreadcrumbGenerator::class)
            ->addArguments([
                new ArrayArgument($config->get('routes')),
                new StringArgument($config->get('routePath')),
            ]);
    }
}

