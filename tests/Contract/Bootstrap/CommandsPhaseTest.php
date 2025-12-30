<?php

use JDS\Bootstrap\Phase\CommandsPhase;
use JDS\Console\CommandRegistry;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Exceptions\Console\ConsoleRuntimeException;
use League\Container\Container;
use League\Container\Definition\DefinitionInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\Contract\Stubs\Console\FakeCommand;

it('1. COMMANDS phase registers command registry without resolving services', function () {
    $container = Mockery::mock(Container::class);

    $definition = Mockery::mock(DefinitionInterface::class);

    $container->shouldReceive('has')
        ->with(CommandRegistryInterface::class)
        ->andReturn(false);

    $container->shouldReceive('addShared')
        ->withAnyArgs()
        ->andReturn($definition);

    $container->shouldNotReceive('get');

    $phase = new CommandsPhase([FakeCommand::class]);
    $phase->bootstrap($container);

    expect(true)->toBeTrue();
});

it('2. COMMANDS phase populates registry with commands', function () {
    $container = new Container();

    $phase = new CommandsPhase([FakeCommand::class]);
    $phase->bootstrap($container);

    /** @var CommandRegistry $registry */
    $registry = $container->get(CommandRegistry::class);

    expect($registry->all())->toContain(FakeCommand::class);

});


it('3. prevents command registration after registry is locked', function () {
    $registry = new CommandRegistry();

    $registry->register(FakeCommand::class);
    $registry->lock();

    expect(fn () => $registry->register(FakeCommand::class))
        ->toThrow(BootstrapInvariantViolationException::class);
});





