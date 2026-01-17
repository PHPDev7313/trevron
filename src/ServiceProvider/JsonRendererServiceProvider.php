<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace JDS\ServiceProvider;

use JDS\Contracts\Http\Rendering\JsonRendererInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Http\Rendering\JsonRenderer;
use League\Container\Container;

final class JsonRendererServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        JsonRendererInterface::class,
    ];

    public function register(Container $container): void
    {
        $container->add(JsonRendererInterface::class, JsonRenderer::class )
            ->setShared(true);
    }
}

