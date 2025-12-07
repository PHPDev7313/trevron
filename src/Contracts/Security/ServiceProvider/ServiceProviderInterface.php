<?php

namespace JDS\Contracts\Security\ServiceProvider;

use League\Container\Container;

interface ServiceProviderInterface
{
	public function register(Container $container): void;

    // optional
//    public function boot(Container $container): void;
}

