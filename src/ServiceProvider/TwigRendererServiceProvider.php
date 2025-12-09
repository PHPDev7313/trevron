<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Rendering\RendererInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Rendering\TwigRenderer;
use League\Container\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRendererServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        Environment::class,
        RendererInterface::class,
    ];

    public function register(Container $container): void
    {

        //
        // 1. Twig Loader
        //
        $container->add(FilesystemLoader::class, function () use ($container) {
            $config = $container->get('config');
            $templatePath = $config->get('templatePath');

            return new FilesystemLoader($templatePath);
        });

        //
        // 2. Twig Environment
        //
        $container->add(Environment::class, function () use ($container) {
            $loader = $container->get(FilesystemLoader::class);

            return new Environment($loader, [
                'cache'       => false,
                'debug'       => (bool)$container->get('config')->get('debug'),
                'auto_reload' => true,
            ]);
        });

        //
        // 3. TwigRenderer binds to RendererInterface
        //
        $container->add(RendererInterface::class, TwigRenderer::class)
            ->addArgument(Environment::class);
    }
}