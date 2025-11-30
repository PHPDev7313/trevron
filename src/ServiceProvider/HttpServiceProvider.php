<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Routing\RouterInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Kernel;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\Middleware\RequestHandler;
use JDS\Http\Middleware\RouterDispatch;
use JDS\Routing\Router;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class HttpServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        RouterInterface::class,
        RequestHandlerInterface::class,
        Kernel::class,
        RouterDispatch::class,
        ExtractRouteInfo::class,
        EventDispatcher::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $config = $this->container->get(Config::class);

        //
        // 1. RouterInterface -> Router
        //
        $this->container->add(RouterInterface::class, Router::class);

        //
        // 2. RequestHandlerInterface -> RequestHandler
        //
        $this->container->add(RequestHandlerInterface::class, RequestHandler::class)
            ->addArgument($this->container);

        //
        // 3. EventDispatcher
        //
        $this->container->add(EventDispatcher::class);

        //
        // 4. HTTP Kernel
        //
        $this->container->add(Kernel::class)
            ->addArguments([
                ($config->isProduction() || $config->isStaging()),
                RequestHandlerInterface::class,
                EventDispatcher::class,
            ]);

        //
        // 5. RouterDispatch middleware
        //
        $this->container->add(RouterDispatch::class)
            ->addArguments([
                RouterInterface::class,
                $this->container
            ]);

        //
        // 6. ExtractRouteInfo middleware
        //
        $this->container->add(ExtractRouteInfo::class)
            ->addArguments([
                new ArrayArgument($config->get('routes')),
                new StringArgument($config->get('routePath')),
                new StringArgument($config->get('basePath')),
                new StringArgument($config->get('baseUrl')),
            ]);
    }
}

