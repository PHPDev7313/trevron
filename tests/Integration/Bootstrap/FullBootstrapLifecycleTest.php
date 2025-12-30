<?php

use FastRoute\Dispatcher;
use JDS\Bootstrap\BootstrapContainer;
use JDS\Bootstrap\BootstrapRunner;
use JDS\Bootstrap\Phase\CommandsPhase;
use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Bootstrap\RoutingBootstrap;
use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Contracts\Console\CommandRegistryInterface;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Routing\ProcessRoutes;
use JDS\Routing\RouteBootstrap;
use League\Container\Container;
use Tests\Contract\Stubs\Console\FakeCommand;
use Tests\Contract\Stubs\Secrets\FakeLockableSecrets;
use Tests\Stubs\Bootstrap\StubPhase;

it('runs full bootstrap lifecycle and enforces all invariants', function () {

    $container = new BootstrapContainer();

    // ----- Secrets registered BEFORE SECRETS phase -----
    $secrets = new FakeLockableSecrets();

    $container->addShared(LockableSecretsInterface::class, fn () => $secrets);
    $container->addShared(SecretsInterface::class, fn () => $secrets);

    // ----- Routing setup -----
    $processedRoutes = ProcessRoutes::process([
        ['GET', '/', [fn () => null]],
    ]);

    $dispatcher = RouteBootstrap::buildDispatcher($processedRoutes);

    $routingBootstrap = new RoutingBootstrap(
        $processedRoutes,
        $dispatcher
    );

    $runner = new BootstrapRunner($container);

    // ---- CONFIG (stub) ----
    $runner->addPhase(new StubPhase(BoostrapPhase::CONFIG));

    // ---- ROUTING ----
    $runner->addPhase(new class($routingBootstrap) implements BootstrapPhaseInterface {
        public function __construct(private RoutingBootstrap $routing) {}

        public function phase(): BoostrapPhase
        {
            return BoostrapPhase::ROUTING;
        }

        public function bootstrap(Container $container): void
        {
            $this->routing->bootstrap();
            $this->routing->lock();

            $container->addShared(
                Dispatcher::class,
                fn () => $this->routing->dispatcher()
            );
        }
    });

    // ---- SECRETS ----
    $runner->addPhase(new SecretsPhase());

    // ---- COMMANDS ----
    $runner->addPhase(new CommandsPhase([
        FakeCommand::class,
    ]));

    // ---- RUN ----
    $runner->run();

    // ---- ASSERT: secrets locked ----
    expect($secrets->isLocked())->toBeTrue();

    // ---- ASSERT: commands registered ----
    $registry = $container->get(CommandRegistryInterface::class);
    expect($registry->all())->toContain(FakeCommand::class);

    // ---- ASSERT: dispatcher available ----
    expect($container->get(Dispatcher::class))
        ->toBeInstanceOf(Dispatcher::class);

    // ---- ASSERT: bootstrap sealed ----
    expect(fn () => $runner->run())
        ->toThrow(BootstrapInvariantViolationException::class);
});



