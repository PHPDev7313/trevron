<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;

class LocationServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container): void
    {
        //
        // 1. Base provider for fetching location data
        //
        $container->add(Locations::class);

        $provider = $container->get(Locations::class);

        //
        // 2. States
        //
        $container->add('states', new ArrayArgument($provider->getStates()));

        //
        // 3. Countries
        //
        $container->add('countries', new ArrayArgument($provider->getCountries()));

        //
        // 4. Cities
        //
        $container->add('cities', new ArrayArgument($provider->getCities()));
    }
}

