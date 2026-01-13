<?php /** @noinspection PhpClassNamingConventionInspection */
declare(strict_types=1);

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Rendering\RendererInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Rendering\TwigRenderer;
use League\Container\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRendererServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        FilesystemLoader::class,
        Environment::class,
        RendererInterface::class,
    ];

    /** @noinspection PhpVariableNamingConventionInspection */
    public function register(Container $container): void
    {
        $config = $container->get(Config::class);
        //
        // 1. Twig Loader
        //
        $container->add(FilesystemLoader::class, function () use ($container, $config) {


            $path = $config->get('templates.path');

            if (!$path || !is_dir($path)) {
                throw new \RuntimeException(
                    'Twig templates path is missing or invalid: {$path}. [Twig:Renderer:Service:Provider].'
                );
            }
            return new FilesystemLoader($path);
        })
        ->setShared(true);

        //
        // 2. Twig Environment
        //
        $container->add(Environment::class, function () use ($container, $config) {
            $loader = $container->get(FilesystemLoader::class);

            return new Environment($loader, [
                'cache'       => false,
                'debug'       => (bool)$config->get('debug'),
                'auto_reload' => true,
            ]);
        })
        ->setShared(true);

        //
        // 3. TwigRenderer binds to RendererInterface
        //
        $container->add(RendererInterface::class, TwigRenderer::class)
            ->addArgument(Environment::class)
            ->setShared(true);
    }
}

