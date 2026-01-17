<?php
declare(strict_types=1);

namespace JDS\ServiceProvider;

use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Contracts\Http\Rendering\HttpRendererInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Http\Rendering\HttpRenderer;
use League\Container\Container;
use RuntimeException;

class HttpRendererServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        HttpRendererInterface::class,
    ];

    public function register(Container $container): void
    {
        // ------------------------------------------------
        // Bootstrap invariant: TemplateEngine must exist
        // ------------------------------------------------
        if (!$container->has(TemplateEngineInterface::class)) {
            throw new RuntimeException(
                'Template Engine Interface not registered. Http Renderer cannot boot. [Http:Renderer:Service:Provider].'
            );
        }

        $container->add(HttpRendererInterface::class, HttpRenderer::class)
            ->addArgument(TemplateEngineInterface::class)
            ->setShared(true);
    }
}

