<?php

use JDS\Configuration\Config;
use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EditSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;
use JDS\Console\Command\Secrets\ValidateSecretCommand;
use JDS\Exceptions\Configuration\ConfigRuntimeException;
use JDS\ServiceProvider\ConfigSecretsServiceProvider;
use League\Container\Container;

beforeEach(function () {
    $this->container = new Container();
    $this->provider = new ConfigSecretsServiceProvider();
});

it('1. throws if Config is not registered', function () {
    $this->provider->register($this->container);
})->throws(
    ConfigRuntimeException::class,
    "Configuration data must be loaded first"
);

it('2. throws if basePath is missing', function () {
    $this->container->add(Config::class, new Config([
        'app.secret.key' => 'test-key'
    ]));
    $this->provider->register($this->container);
})->throws(
    ConfigRuntimeException::class,
    "basePath"
);

it('3. throws if app.secret.key is missing', function () {
    $this->container->add(Config::class, new Config([
        'basePath' => '/app',
    ]));

    $this->provider->register($this->container);
})->throws(
    ConfigRuntimeException::class,
    'app.secret.key'
);

it('4. registers all secrets console commands', function () {
    $this->container->add(Config::class, new Config([
        'basePath' => '/app',
        'app.secret.key' => 'super-secret',
    ]));

    $this->provider->register($this->container);

    expect($this->container->has(EncryptSecretsCommand::class))->toBeTrue();
    expect($this->container->has(DecryptSecretsCommand::class))->toBeTrue();
    expect($this->container->has(ValidateSecretCommand::class))->toBeTrue();
    expect($this->container->has(EditSecretsCommand::class))->toBeTrue();
});

it('5. resolves default secrets paths relative to basePath', function () {
    $this->container->add(Config::class, new Config([
        'basePath' => '/app',
        'app.secret.key' => 'super-secret',
    ]));

    $this->provider->register($this->container);

    $encrypt = $this->container->get(EncryptSecretsCommand::class);
    $validate = $this->container->get(ValidateSecretCommand::class);

    $encryptArgs = (new ReflectionClass($encrypt))
        ->getConstructor()
        ->getParameters();

    expect($encryptArgs)->toHaveCount(3);
});

it('6. registers commands as shared services', function () {
    $this->container->add(Config::class, new Config([
        'basePath' => '/app',
        'app.secret.key' => 'super-secret',
    ]));

    $this->provider->register($this->container);

    expect(
        $this->container->get(EncryptSecretsCommand::class)
    )->toBe(
        $this->container->get(EncryptSecretsCommand::class)
    );
});






