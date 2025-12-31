<?php

use Doctrine\DBAL\Connection;
use JDS\Bootstrap\BootstrapRunner;
use JDS\Bootstrap\Phase\SecretsPhase;
use JDS\Configuration\Config;
use JDS\Contracts\Bootstrap\BoostrapPhase;
use JDS\Contracts\Security\LockableSecretsInterface;
use JDS\Exceptions\Database\DatabaseRuntimeException;
use JDS\ServiceProvider\DatabaseConnectionServiceProvider;
use League\Container\Container;
use Tests\Stubs\Bootstrap\NullBootstrapPhase;
use Tests\Stubs\Security\TestLockableSecrets;

it('enforces secrets lock before database provider registration', function () {

    $container = new Container();

    // --------------------------------------------------
    // Register Config (non-sensitive DB structure)
    // --------------------------------------------------
    $container->add(Config::class, new Config([
        'db' => [
            'driver' => 'pdo_sqlite',
            'host'   => 'localhost',
            'port'   => 0,
            'dbname' => ':memory:',
        ],
    ]));

    // --------------------------------------------------
    // Register Secrets (UNLOCKED)
    // --------------------------------------------------
    $secrets = new TestLockableSecrets([
        'db.user'     => 'test',
        'db.password' => 'secret',
    ]);

    $container->add(LockableSecretsInterface::class, $secrets);

    // --------------------------------------------------
    // Assert DB provider CANNOT register before SecretsPhase
    // --------------------------------------------------
    expect(fn () =>
    (new DatabaseConnectionServiceProvider())->register($container)
    )->toThrow(DatabaseRuntimeException::class);

    // --------------------------------------------------
    // Run FULL bootstrap (with no-op phases)
    // --------------------------------------------------
    $runner = new BootstrapRunner($container);

    $runner->addPhase(new NullBootstrapPhase(BoostrapPhase::CONFIG));
    $runner->addPhase(new NullBootstrapPhase(BoostrapPhase::ROUTING));
    $runner->addPhase(new SecretsPhase());
    $runner->addPhase(new NullBootstrapPhase(BoostrapPhase::COMMANDS));

    $runner->run();

    expect($secrets->isLocked())->toBeTrue();

    // --------------------------------------------------
    // Now DB provider registration MUST succeed
    // --------------------------------------------------
    (new DatabaseConnectionServiceProvider())->register($container);

    // --------------------------------------------------
    // And DB connection MUST resolve
    // --------------------------------------------------
    $connection = $container->get(Connection::class);

    expect($connection)->toBeInstanceOf(Connection::class);
});
