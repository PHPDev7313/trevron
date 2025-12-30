<?php

use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;
use Tests\Stubs\Bootstrap\Phase\TestLockableSecrets;

beforeEach(function () {
    $this->container = new Container();
    $this->phase = new SecretsPhase();
});

it('1. fails if LockableSecretsInterface is not registered', function () {
    $this->phase->bootstrap($this->container);
})->throws(
    BootstrapInvariantViolationException::class,
    "Lockable secrets service must be registered"
);

it('2. locks secrets when executed once', function () {
    $secrets = new TestLockableSecrets();

    $this->container->add(
        LockableSecretsInterface::class,
        $secrets
    );

    $this->phase->bootstrap($this->container);

    expect($secrets->islocked())->toBeTrue();
});

it('3. fails if secrets are already locked', function () {
    $secrets = new TestLockableSecrets();
    $secrets->lock(); // pre-locked

    $this->container->add(
        LockableSecretsInterface::class,
        $secrets
    );

    $this->phase->bootstrap($this->container);


})->throws(
    BootstrapInvariantViolationException::class,
    "SECRETS phase executed more than once"
);

it('4. cannot be executed twice', function () {
    $secrets = new TestLockableSecrets();

    $this->container->add(
        LockableSecretsInterface::class,
        $secrets
    );

    // first run - ok
    $this->phase->bootstrap($this->container);
    expect($secrets->islocked())->toBeTrue();

    // second run - must fail
    expect(fn () => $this->phase->bootstrap($this->container))
        ->toThrow(BootstrapInvariantViolationException::class);
});






