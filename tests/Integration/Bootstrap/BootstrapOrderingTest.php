<?php

use JDS\Bootstrap\BootstrapRunner;
use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;
use Tests\Stubs\Bootstrap\Phase\TestCommandsPhase;
use Tests\Stubs\Bootstrap\Phase\TestRoutingPhase;
use Tests\Stubs\Bootstrap\Phase\TestSecretsProviderPhase;

it('1. boots successfully when phases are registered in correct order', function () {
    $container = new Container();
    $runner = new BootstrapRunner($container);

    $runner->addPhase(new TestSecretsProviderPhase());  // CONFIG (register secrets)
    $runner->addPhase(new TestRoutingPhase());          // ROUTING
    $runner->addPhase(new SecretsPhase());              // SECRETS
    $runner->addPhase(new TestCommandsPhase());         // COMMANDS

    $runner->run();

    expect(true)->toBeTrue();
});

it('2. fails when SECRETS phase is registered before ROUTING', function () {
    $container = new Container();
    $runner = new BootstrapRunner($container);

    $runner->addPhase(new TestSecretsProviderPhase());  // CONFIG
    $runner->addPhase(new SecretsPhase());              // ❌ too early
    $runner->addPhase(new TestRoutingPhase());          // ROUTING
    $runner->addPhase(new TestCommandsPhase());         // COMMANDS

    $runner->run();
})->throws(
    BootstrapInvariantViolationException::class,
    "out of order"
);

it('3. fails when SECRETS phase is missing', function () {
    $container = new Container();
    $runner = new BootstrapRunner($container);

    $runner->addPhase(new TestSecretsProviderPhase());
    $runner->addPhase(new TestRoutingPhase());
    $runner->addPhase(new TestCommandsPhase());

    $runner->run();
})->throws(
    BootstrapInvariantViolationException::class,
    "Required bootstrap phase missing"
);

it('4. fails when SECRETS phase is registered twice', function () {
    $container = new Container();
    $runner = new BootstrapRunner($container);

    $runner->addPhase(new TestSecretsProviderPhase());
    $runner->addPhase(new TestRoutingPhase());
    $runner->addPhase(new SecretsPhase());
    $runner->addPhase(new SecretsPhase()); // ❌ duplicate
    $runner->addPhase(new TestCommandsPhase());

    $runner->run();
})->throws(
    BootstrapInvariantViolationException::class,
    "Duplicate bootstrap phase"
);



