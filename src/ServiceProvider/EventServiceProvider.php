<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\EventSubscriber\HttpKernelSubscriber;
use League\Container\Container;

class EventServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container): void
    {
        $dispatcher = $container->get(EventDispatcher::class);

        $dispatcher->addSubscriber(
            $container->get(HttpKernelSubscriber::class)
        );
    }
}

