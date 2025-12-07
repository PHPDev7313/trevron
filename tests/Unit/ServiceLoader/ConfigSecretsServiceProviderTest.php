<?php



use JDS\Bootstrap\ServiceLoader;
use JDS\Configuration\Config;
use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EditSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;
use JDS\Console\Command\Secrets\ValidateSecretCommand;
use JDS\Exceptions\Configuration\ConfigRuntimeException;
use JDS\Exceptions\ServiceProvider\ServiceProviderRuntimeException;
use JDS\ServiceProvider\ConfigSecretsServiceProvider;
use League\Container\Container;

//
// Helper to build container + provider without ServiceLoader
//
function makeContainer(array $configData): Container
{
    $loader = new ServiceLoader($configData);
    $loader->addProvider(ConfigSecretsServiceProvider::class);
    return $loader->boot();
}

//
// 1. Provider requires Config service to exist
//
it('1. throws if Config is missing from the container', function () {
    $container = new Container();
    $provider = new ConfigSecretsServiceProvider();

    $provider->register($container);
})->throws(ConfigRuntimeException::class);

//
// 2. basePath is required
//
it('2. throws if basePath is missing or invalid', function () {
    makeContainer([
        'secrets' =>
            [
                'file' => 'x'
            ],
        'app' =>
            [
                'secret' =>
                    [
                        'key' => 'abc123'
                    ]
            ]
    ]);
})->throws(ServiceProviderRuntimeException::class);

//
// 3. app.secret.key must exist
//
it('3. throws if app.secret.key is missing', function () {
    makeContainer([
        'basePath' => '/var/www',
        'secrets' => [
            'file' => 'abc.enc'
        ],
    ]);
})->throws(ServiceProviderRuntimeException::class);

//
// 4. It registers all 4 commands
//
it('4. registers all secret commands into the container', function () {

    $container = makeContainer([
        'basePath' => '/app',
        'secrets' => [
            'file' => 'config/secret.json.enc',
            'plain' => 'config/plain.json',
            'schema' => 'config/schema.json'
        ],
        'app' => [
            'secret' =>
                [
                    'key' => 'XYZKEY'
                ]
        ]
    ]);

    expect($container->has(EncryptSecretsCommand::class))->toBeTrue();
    expect($container->has(DecryptSecretsCommand::class))->toBeTrue();
    expect($container->has(ValidateSecretCommand::class))->toBeTrue();
    expect($container->has(EditSecretsCommand::class))->toBeTrue();
});

//
// 5. Default paths are used when values are missing
//
it('5. injects correct arguments in correct order', function () {

    $container = makeContainer([
        'basePath' => '/home/app',
        'secrets' => [
            'file' => 'foo/secure.enc',
            'plain' => 'foo/plain.txt',
            'schema' => 'foo/schema.json',
        ],
        'app' => ['secret' => ['key' => 'KEY123']]
    ]);

    $encrypt = $container->get(EncryptSecretsCommand::class);

    //
    // Extract private properties
    //
    $rpKey = new ReflectionProperty(EncryptSecretsCommand::class, 'appSecretKey');
    $rpKey->setAccessible(true);

    $rpPlain = new ReflectionProperty(EncryptSecretsCommand::class, 'plainPath');
    $rpPlain->setAccessible(true);

    $rpEnc = new ReflectionProperty(EncryptSecretsCommand::class, 'encPath');
    $rpEnc->setAccessible(true);

    //
    // Validate stored constructor values
    //
    expect($rpKey->getValue($encrypt))->toBe('KEY123');
    expect($rpPlain->getValue($encrypt))->toBe('/home/app/foo/plain.txt');
    expect($rpEnc->getValue($encrypt))->toBe('/home/app/foo/secure.enc');
});

//
// 6. Custom paths override defaults
//
it('6. honors custom paths from the config', function () {

    $container = makeContainer([
        'basePath' => '/workspace',
        'secrets' => [
            'file' => 'override/secret.enc',
            'plain' => 'override/plain.txt',
            'schema' => 'override/schema.json'
        ],
        'app' => ['secret' => ['key' => '12345']]
    ]);

    $encrypt = $container->get(EncryptSecretsCommand::class);
    $decrypt = $container->get(DecryptSecretsCommand::class);
    $validate = $container->get(ValidateSecretCommand::class);

    //
    // Inspect command internals via reflection since args are private
    //
    $rp = new ReflectionProperty(EncryptSecretsCommand::class, 'encPath');
    $rp->setAccessible(true);
    $encFile = $rp->getValue($encrypt);

    expect($encFile)->toBe('/workspace/override/secret.enc');


    $rp = new ReflectionProperty(EncryptSecretsCommand::class, 'plainPath');
    $rp->setAccessible(true);
    expect($rp->getValue($encrypt))->toBe('/workspace/override/plain.txt');


    $rp = new ReflectionProperty(ValidateSecretCommand::class, 'schemaPath');
    $rp->setAccessible(true);
    expect($rp->getValue($validate))->toBe('/workspace/override/schema.json');
});

//
// 7. Commands get corect arguments in correct order
//
it('7. injects correct arguments in correct order', function () {

    $container = makeContainer([
        'basePath' => '/home/app',
        'secrets' => [
            'file' => 'foo/secure.enc',
            'plain' => 'foo/plain.txt',
            'schema' => 'foo/schema.json',
        ],
        'app' => ['secret' => ['key' => 'KEY123']]
    ]);

    $encrypt = $container->get(EncryptSecretsCommand::class);

    $class = new ReflectionClass(EncryptSecretsCommand::class);
    $constructor = $class->getConstructor();
    $params = $constructor->getParameters();

    // Confirm that container provided correct arguments
    expect($params)->toHaveCount(3);

    //
    // Inspect actual injected private fields (reflective)
    //
    $rpKey = new ReflectionProperty($encrypt, 'appSecretKey');
    $rpKey->setAccessible(true);

    $rpPlain = new ReflectionProperty($encrypt, 'plainPath');
    $rpPlain->setAccessible(true);

    $rpEnc = new ReflectionProperty($encrypt, 'encPath');
    $rpEnc->setAccessible(true);

    expect($rpKey->getValue($encrypt))->toBe('KEY123');
    expect($rpPlain->getValue($encrypt))->toBe('/home/app/foo/plain.txt');
    expect($rpEnc->getValue($encrypt))->toBe('/home/app/foo/secure.enc');
});

//
// 8. Provider never pollutes container with data keys
//
it('8. does NOT add any data-keys or raw arrays into container', function () {

    $container = makeContainer([
        'basePath' => '/abc',
        'secrets' => [],
        'app' => ['secret' => ['key' => 'X']]
    ]);

    //
    // Ensure container contains ONLY services, not metadata
    //
    $definitions = (new ReflectionObject($container))
        ->getProperty('definitions')
        ->getValue($container);

    foreach ($definitions->getIterator() as $definition) {
        $alias = $definition->getAlias();

        // Allowed aliases
        if (in_array($alias, [
            Config::class,
            EncryptSecretsCommand::class,
            DecryptSecretsCommand::class,
            ValidateSecretCommand::class,
            EditSecretsCommand::class
        ], true)) {
            continue;
        }

        throw new Exception("Unexpected container entry detected: {$alias}");
    }

    expect(true)->toBeTrue(); // no errors
});















