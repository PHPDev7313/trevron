<?php

declare(strict_types=1);

namespace JDS\Bootstrap\Invariant;

use FastRoute\Dispatcher;
use JDS\Contracts\Bootstrap\BootstrapInvariantInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

final class RoutingInvariant implements BootstrapInvariantInterface
{

    public function assert(Container $container): void
    {
        if (!$container->has(Dispatcher::class)) {
            throw new BootstrapInvariantViolationException(
                "Routing dispatcher missing: " . Dispatcher::class
            );
        }
    }
}