<?php

namespace JDS\ServiceProvider;

use JDS\Diff\DiffEngine;
use JDS\Diff\Strategy\ArrayDiffStrategy;
use JDS\Diff\Strategy\ObjectDiffStrategy;
use JDS\Diff\Strategy\ScalarDiffStrategy;
use JDS\Diff\Strategy\TextDiffStrategy;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;

class DiffServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Declare what this provider offers.
     * IMPORTANT: These MUST be the identifiers used in the container
     *
     */
    protected array $provides = [
        DiffEngine::class,
    ];

    /**
     * REQUIRED BY AbstractServiceProvider
     *
     * The container will call this to determine if this
     * provider is responsible for registering a given $id.
     */
    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $this->container->add(DiffEngine::class, function () {
            $engine = new DiffEngine();

            // Register default strategies
            $engine->addStrategy('scalar', new ScalarDiffStrategy());
            $engine->addStrategy('array', new ArrayDiffStrategy());
            $engine->addStrategy('object', new ObjectDiffStrategy());
            $engine->addStrategy('text', new TextDiffStrategy());
            return $engine;
        });
    }
}