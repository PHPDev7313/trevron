<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LocationServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array<string>
     */
    protected array $provides = [
        ClientLocationProvider::class,
        'states',
        'countries',
        'cities',
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        //
        // 1. Base provider for fetching locaiton data
        //
        $this->container->add(ClientLocationProvider::class);


        $provider = $this->container->get(ClientLocationProvider::class);

        //
        // 2. States
        //
        $this->container->add('states', new ArrayArgument($provider->getStates()));

        //
        // 3. Countries
        //
        $this->container->add('countries', new ArrayArgument($provider->getCountries()));

        //
        // 4. Cities
        //
        $this->container->add('cities', new ArrayArgument($provider->getCities()));
    }
}