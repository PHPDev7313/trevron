<?php /** @noinspection PhpClassNamingConventionInspection */
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 5, 2026
 *
 * FINAL: January 13, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

declare(strict_types=1);

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use League\Container\Container;
use RuntimeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRendererServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        FilesystemLoader::class,
        Environment::class,
        TemplateEngineInterface::class,
    ];

    /** @noinspection PhpVariableNamingConventionInspection */
    public function register(Container $container): void
    {
        // -----------------------------------------
        // Bootstrap invariant: Config must exist
        // -----------------------------------------
        if (!$container->has(Config::class)) {
            throw new RuntimeException(
                'Config service missing: Twig Renderer Service Provider cannot boot. [Twig:Renderer:Service:Provider].'
            );
        }

        /** @var Config $config */
        $config = $container->get(Config::class);

        // --------------------------------------------
        // Resolve and normalize template root ONCE
        // --------------------------------------------
        $basePath = rtrim((string) $config->get('app.basePath'), '/');
        $templateDir = trim($config->twigTemplateRoot(), '/');

        if ($basePath === '') {
            throw new RuntimeException(
                'app basePath is missing or empty. [Twig:Renderer:Service:Provider].'
            );
        }

        $templatePath = $basePath . '/' . $templateDir;
        $templatePath = str_replace('\\', '/', $templatePath);

        if (!is_dir($templatePath)) {
            throw new RuntimeException(
                "Twig templates path is invalid or does not exist: {$templatePath}. [Twig:Renderer:Service:Provider]."
            );
        }

        //
        // 1. FilesystemLoader IMMUTABLE (Twig Loader)
        //
        $container->add(FilesystemLoader::class, function () use ($templatePath) {
            return new FilesystemLoader($templatePath);
        })
        ->setShared(true);

        //
        // 2. Twig Environment
        //
        $container->add(Environment::class, function () use ($container, $config) {
            $loader = $container->get(FilesystemLoader::class);

            $twig =  new Environment($loader, [
                'cache'       => false,
                'debug'       => $config->isDevelopment(),
                'auto_reload' => true,
            ]);

            // ---------------------------------------------
            // Register Twig extension declared in config
            // ---------------------------------------------
            foreach ($config->getArray('twig.extensions') as $extensionClass) {
                if (!$container->has($extensionClass)) {
                    throw new RuntimeException(
                        "Twig extension {$extensionClass} is not registered. [Twig:Renderer:Service:Provider]."
                    );
                }

                $twig->addExtension($container->get($extensionClass));
            }

            return $twig;
        })
        ->setShared(true);
    }
}

