<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Contracts\Http\Controller\ControllerDispatchResultInterface;
use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Error\Response\ErrorResponder;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Kernel;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\Middleware\MiddlewareResolver;
use JDS\Http\Middleware\RequestHandler;
use JDS\Routing\Route;
use League\Container\Container;

class HttpServiceProvider  implements ServiceProviderInterface
{

    public function register(Container $container): void
    {

        $container->add(MiddlewareResolver::class)
            ->addArguments([
                $container,
                ExtractRouteInfo::class,
            ])
            ->setShared(true);

        $container->add(MiddlewareResolverInterface::class)
            ->addArgument(MiddlewareResolver::class)
            ->setShared(true);

        // EventDispatcher
        $container->add(EventDispatcher::class);

        // Kernel
        $container->add(Kernel::class)
            ->addArguments([
                MiddlewareResolverInterface::class,
                ControllerDispatchResultInterface::class,
                EventDispatcher::class,
                ErrorResponder::class
            ])
            ->setShared(true);
    }
}



//        $config = $container->get(Config::class);

//    protected array $provides = [
//        ControllerDispatchResultInterface::class,
//        RequestHandlerInterface::class,
//        ExtractRouteInfo::class,
//        EventDispatcher::class,
//        Kernel::class,
//    ];
//
//
//

//    public function provides(string $id): bool
//    {
//        return in_array($id, $this->provides, true);
//    }
//        //
//        // 6. ExtractRouteInfo middleware
//        //
//        $container->add(ExtractRouteInfo::class)
//            ->addArguments([
//                new ArrayArgument($config->get('routes')),
//                new StringArgument($config->get('routePath')),
//                new StringArgument($config->get('basePath')),
//                new StringArgument($config->get('baseUrl')),
//            ]);
//    }

//        //
//        // 5. RouterDispatch middleware
//        //
//        $container->add(RouterDispatch::class)
//            ->addArguments([
//                ControllerDispatchResultInterface::class,
//                $container
//            ]);

//        //
//        // 4. HTTP Kernel
//        //
//        $container->add(Kernel::class)
//            ->addArguments([
//                ($config->isProduction() || $config->isStaging()),
//                RequestHandlerInterface::class,
//                EventDispatcher::class,
//            ]);



