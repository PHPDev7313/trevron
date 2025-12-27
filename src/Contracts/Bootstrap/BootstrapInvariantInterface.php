<?php

declare(strict_types=1);

namespace JDS\Contracts\Bootstrap;

use League\Container\Container;

interface BootstrapInvariantInterface
{
    public function assert(Container $container): void;
}

