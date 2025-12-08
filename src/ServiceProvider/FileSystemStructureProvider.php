<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\DirectoryInitializationState;
use JDS\FileSystem\DirectoryStructureInitializer;
use League\Container\Container;

class FileSystemStructureProvider implements ServiceProviderInterface
{

    public function register(Container $container): void
    {
        $basePath = $container->get(Config::class)->get('basePath');

        $container->add(DirectoryInitializationState::class)
            ->addArgument($basePath . '/storage/system/initialized.json');

        $container->add(DirectoryStructureInitializer::class)
            ->addArgument(DirectoryInitializationState::class);

        // run it immediately
        $initializer = $container->get(DirectoryStructureInitializer::class);

        $required = require $basePath . '/config/paths.required.php';

        $initializer->initialize($basePath, $required);
    }
}