<?php

use Doctrine\DBAL\Connection;
use JDS\Dbal\ConnectionFactory;
use JDS\ServiceProvider\DatabaseConnectionServiceProvider;
use League\Container\Container;

beforeEach(function () {
    $this->container = new Container();

    //
    // mock config service
    //
    $this->container->add('config', new class {
        public function get(string $key)
        {
            if ($key === 'db') {
                return [
                    'dbname' => 'testdb',
                    'user' => 'user',
                    'password' => 'pass',
                    'host' => 'localhost',
                    'port' => 3306,
                    'driver' => 'pdo_mysql'
                ];
            }
            return null;
        }
    });

    //
    // register provider (but not bootstrapped)
    //
    $provider = new DatabaseConnectionServiceProvider($this->container);
    $provider->register();
});

it('it registers the ConnectionFactory service', function () {
    expect($this->container->has(ConnectionFactory::class))->toBeTrue();

   $factory = $this->container->get(ConnectionFactory::class);
   expect($factory)->toBeInstanceOf(ConnectionFactory::class);
});

it('it registers the Doctrine Connection as shared', function () {
    expect($this->container->has(Connection::class))->toBeTrue();

    $db1 = $this->container->get(Connection::class);
    $db2 = $this->container->get(Connection::class);

    expect($db1)->toBeInstanceOf(Connection::class);
    expect($db1)->toBe($db2); // shared instance
});

it('provider lists the services it provides', function () {
    $provider = new DatabaseConnectionServiceProvider($this->container);
    expect($provider->provides(ConnectionFactory::class))->toBeTrue();
    expect($provider->provides(Connection::class))->toBeTrue();
    expect($provider->provides('non-existent'))->toBeFalse();
});






