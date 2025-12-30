<?php

use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;

beforeEach(function () {
    $this->container = new Container();
    $this->phase = new SecretsPhase();
});

it('1. throws if LockableSecretsInterface is not registred', function () {
    $this->phase->bootstrap($this->container);
})->throws(
    BootstrapInvariantViolationException::class,
    "Lockable secrets service must be registered before SECRETS phase."
);

it('2. throws if secrets are already locked', function () {
    $secrets = Mockery::mock(LockableSecretsInterface::class);
    $secrets->shouldReceive('isLocked')->once()->andReturn(true);

    $this->container->add(LockableSecretsInterface::class, $secrets);

    $this->phase->bootstrap($this->container);
})->throws(
    BootstrapInvariantViolationException::class,
    "SECRETS phase executed more than once."
);

it('3. does not unlock or re-lock secrets after locking', function () {
    $secrets = Mockery::mock(LockableSecretsInterface::class);

    $secrets->shouldReceive('isLocked')->once()->andReturn(false);
    $secrets->shouldReceive('lock')->once();

    $this->container->add(LockableSecretsInterface::class, $secrets);

    $this->phase->bootstrap($this->container);

    // simulate second run
    $secrets->shouldReceive('isLocked')->once()->andReturn(true);

    expect(fn () => $this->phase->bootstrap($this->container))
        ->toThrow(
            BootstrapInvariantViolationException::class,
            "SECRETS phase executed more than once."
        );
});






