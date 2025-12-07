<?php

namespace Tests\Unit\ServiceLoader;


use http\Exception\RuntimeException;
use JDS\Bootstrap\ServiceLoader;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\ServiceProvider\ServiceProviderRuntimeException;
use League\Container\Container;

class ServiceLoaderTest {
    public static array $events = [];
}

class p1 implements ServiceProviderInterface {
    public function register(Container $c): void
    {
        ServiceLoaderTest::$events[] = 'first';
    }
}

class p2 implements ServiceProviderInterface {
    public function register(Container $c): void
    {
        ServiceLoaderTest::$events[] = 'second';
    }
}

it('executes providers in order added', function () {
    ServiceLoaderTest::$events = [];

    $loader = new ServiceLoader([]);
    $loader->addProvider(P2::class);
    $loader->addProvider(p1::class);

    $loader->boot();

    expect(ServiceLoaderTest::$events)->toEqual(['second', 'first']);
});

class WrongProvider {}

it('throws if provider does not implement ServiceProviderInterface', function () {
    $loader = new ServiceLoader([]);

    $loader->addProvider(WrongProvider::class);

    $loader->boot();
})->throws(ServiceProviderRuntimeException::class);

it('throws when provider class does not exist', function () {
    $loader = new ServiceLoader([]);

    $loader->addProvider('DoesNotExist');

    $loader->boot();
})->throws(ServiceProviderRuntimeException::class);

class ExplodingProvider implements ServiceProviderInterface {
    public function register(Container $c): void
    {
        throw new \RuntimeException("boom");
    }
}

it('wraps exceptions thrown inside provider register()', function () {
    $loader = new ServiceLoader([]);

    $loader->addProvider(ExplodingProvider::class);

    $loader->boot();
})->throws(ServiceProviderRuntimeException::class);




