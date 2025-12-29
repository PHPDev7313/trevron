<?php

use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use League\Container\Container;
use Tests\Contract\Stubs\Secrets\FakeLockableSecrets;

it('1. fails if secrets are not registered before SECRETS phase', function () {
    $container = new Container();
    $phase = new SecretsPhase();

    expect(fn () => $phase->bootstrap($container))
        ->toThrow(BootstrapInvariantViolationException::class);
});

it('2. fails if secrets implementation is not lockable', function () {
    $container = new Container();

    $container->addShared(SecretsInterface::class, fn () => new class implements SecretsInterface {
        public function get(string $path, mixed $default = null): mixed { return null; }
        public function all(): array { return []; }
        public function has(string $path): bool { return false; }
    });

    $phase = new SecretsPhase();

    expect(fn () => $phase->bootstrap($container))
        ->toThrow(BootstrapInvariantViolationException::class);
});

it('3. locks secrets during SECRETS phase', function () {
    $container = new Container();

    $secrets = new FakeLockableSecrets();

    $container->addShared(SecretsInterface::class, fn () => $secrets);
    $container->addShared(LockableSecretsInterface::class, fn () => $secrets);

    $phase = new SecretsPhase();
    $phase->bootstrap($container);

    expect($secrets->islocked())->toBeTrue();
});

it('4. prevents secrets from being locked twice', function () {
    $container = new Container();

    $secrets = new FakeLockableSecrets();

    $container->addShared(SecretsInterface::class, fn () => $secrets);
    $container->addShared(LockableSecretsInterface::class, fn () => $secrets);

    $phase = new SecretsPhase();
    $phase->bootstrap($container);

    expect(fn () => $secrets->lock())
        ->toThrow(BootstrapInvariantViolationException::class);
});



