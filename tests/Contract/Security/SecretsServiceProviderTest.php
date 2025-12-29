<?php

use JDS\Contracts\Security\SecretsConfigInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\ServiceProvider\SecretsServiceProvider;
use League\Container\Container;

it('prevents SecretsServiceProvider from registering twice', function () {
    $container = new Container();

    // Fake config so provider can register
    $container->addShared(SecretsConfigInterface::class, fn () => Mockery::mock(SecretsConfigInterface::class));

    $provider = new SecretsServiceProvider();

    // First registeration OK
    $provider->register($container);

    // Second registration forbidden
    expect(fn () => $provider->register($container))
        ->toThrow(BootstrapInvariantViolationException::class);
});






