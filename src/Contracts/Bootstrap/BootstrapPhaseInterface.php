<?php

namespace JDS\Contracts\Bootstrap;

use League\Container\Container;

interface BootstrapPhaseInterface
{
    public function bootstrap(Container $container): void;
}


