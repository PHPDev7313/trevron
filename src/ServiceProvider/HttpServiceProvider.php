<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Http\Controller\ControllerDispatchResultInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Kernel;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\Middleware\RequestHandler;
use JDS\Http\Middleware\RouterDispatch;
use JDS\Routing\Route;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class HttpServiceProvider  implements ServiceProviderInterface
{
    protected array $provides = [
        ControllerDispatchResultInterface::class,
        RequestHandlerInterface::class,
        RouterDispatch::class,
        ExtractRouteInfo::class,
        EventDispatcher::class,
        Kernel::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(Container $container): void
    {
        $config = $container->get(Config::class);

        //
        // 1. RouterInterface -> Router
        //
        $container->add(ControllerDispatchResultInterface::class, Route::class);

        //
        // 2. RequestHandlerInterface -> RequestHandler
        //
        $container->add(RequestHandlerInterface::class, RequestHandler::class)
            ->addArgument($container);

        //
        // 3. EventDispatcher
        //
        $container->add(EventDispatcher::class);

        //
        // 4. HTTP Kernel
        //
        $container->add(Kernel::class)
            ->addArguments([
                ($config->isProduction() || $config->isStaging()),
                RequestHandlerInterface::class,
                EventDispatcher::class,
            ]);

        //
        // 5. RouterDispatch middleware
        //
        $container->add(RouterDispatch::class)
            ->addArguments([
                ControllerDispatchResultInterface::class,
                $container
            ]);

        //
        // 6. ExtractRouteInfo middleware
        //
        $container->add(ExtractRouteInfo::class)
            ->addArguments([
                new ArrayArgument($config->get('routes')),
                new StringArgument($config->get('routePath')),
                new StringArgument($config->get('basePath')),
                new StringArgument($config->get('baseUrl')),
            ]);
    }
}

