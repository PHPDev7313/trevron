<?php

use JDS\Console\CommandRegistry;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Exceptions\Console\ConsoleRuntimeException;
use Tests\Contract\Stubs\Console\FakeCommand;
use Tests\Contract\Stubs\Console\InvalidClass;

it('1. registers valid command classes', function () {
    $registry = new CommandRegistry();

    $registry->register(FakeCommand::class);

    expect($registry->all())->toContain(FakeCommand::class);
});

it('2. rejects non-existent command classes', function () {
    $registry = new CommandRegistry();

    expect(fn () => $registry->register('Does\\Not\\Exist'))
        ->toThrow(ConsoleRuntimeException::class);
});

it('3. rejects classes not implementing CommandInterface', function () {
    $registry = new CommandRegistry();

    expect(fn () => $registry->register(InvalidClass::class))
        ->toThrow(ConsoleRuntimeException::class);
});

it('4. ignores duplicate command registration', function () {
    $registry = new CommandRegistry();

    $registry->register(FakeCommand::class);
    $registry->register(FakeCommand::class);

    expect($registry->all())->toHaveCount(1);
});

it('5. returns only command class names', function () {
    $registry = new CommandRegistry();

    $registry->register(FakeCommand::class);

    $commands = $registry->all();

    expect($commands[0])->toBeString();
    expect(is_subclass_of($commands[0], CommandInterface::class))->toBeTrue();
});




