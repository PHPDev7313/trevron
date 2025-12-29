<?php

use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Security\Secrets;

it('1. locks secrets after bootstrap', function () {

    $secrets = new Secrets([
        'db' => ['user' => 'u', 'pass' => 'p'],
    ]);

    $secrets->lock();

    expect($secrets->isLocked())->toBeTrue();
});

it('2. prevents locking secrets twice', function () {

    $secrets = new Secrets([
        'db' => ['user' => 'u'],
    ]);

    $secrets->lock();

    expect(fn () => $secrets->lock())
        ->toThrow(BootstrapInvariantViolationException::class);
});

it('3. allows reading secrets after lock', function () {

    $secrets = new Secrets([
        'db' => ['user' => 'u'],
    ]);

    $secrets->lock();

    expect($secrets->get('db.user'))->toBe('u');
});







