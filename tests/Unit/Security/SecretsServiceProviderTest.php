<?php

use JDS\Contracts\Security\SecretsConfigInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\Secrets;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;
use JDS\ServiceProvider\SecretsServiceProvider;
use League\Container\Container;

it('1. registers SecretsInterface and aliases and returns shared instance', function () {

    $dir = sys_get_temp_dir() . '/jds_secrets_' . bin2hex(random_bytes(6));
    @mkdir($dir, 0777, true);

    // ---- 1) Create schema ----
    $schemaPath = $dir . '/secrets.schema.json';
    file_put_contents($schemaPath,
        json_encode([
            'db' => [
                'user' => true,
                'password' => true,
            ],
        ], JSON_THROW_ON_ERROR)
    );

    // ---- 2) Create encrypted secrets file ----
    $secretsPath = $dir . '/secrets.json.enc';

    // 32-byte key, base64 encoded (match your APP_SECRET_KEY style)
    $rawKey = random_bytes(32);
    $keyB64 = base64_encode($rawKey);

    $crypto = SecretsCrypto::fromBase64($keyB64);
    $manager = new SecretsManager($secretsPath, $crypto);

    // This line MUST match how your framework persists secrets.
    // Replace saveEncrypted(...) with the real method your SecretsManager/Encrypt command uses.
    $plaintextSecrets = ['db' => ['user' => 'u', 'password' => 'p']];

    // ---- IMPORTANT: pick the method that exists in your codebase ----
    // Option A (common): $manager->save($plaintextSecrets);
    // Option B: $manager->encryptAndWrite($plaintextSecrets);
    // Option C: use the same method called by EncryptSecretsCommand internally.

    $manager->save($plaintextSecrets);

    // ---- 3) Container + config contract ----
    $container = new Container();

    $config = Mockery::mock(SecretsConfigInterface::class);
    $config->shouldReceive('secretsFile')->andReturn($secretsPath);
    $config->shouldReceive('schemaFile')->andReturn($schemaPath);
    $config->shouldReceive('appKeyBase64')->andReturn($keyB64);

    $container->addShared(SecretsConfigInterface::class, fn () => $config);

    // ---- 4) Register provider ----
    $provider = new SecretsServiceProvider();
    $provider->register($container);

    // ---- 5) Resolve and assert sharing + aliases ----
    $a = $container->get(SecretsInterface::class);
    $b = $container->get(Secrets::class);
    $c = $container->get('secrets');

    expect($a)->toBeInstanceOf(Secrets::class)
        ->and($b)->toBe($a)
        ->and($c)->toBe($a);

    // Optional: verify actual data made it through decrypt + validate
    expect($a->get('db.user'))->toBe('u');
});

it('2. throws when sodium is missing (environment guard)', function () {

    if (extension_loaded('sodium')) {
        $this->markTestSkipped('Sodium extension is installed in this environment; missing-extension guard is not applicable.');
    }

    $dir = sys_get_temp_dir() . '/jds_secrets_' . bin2hex(random_bytes(6));
    @mkdir($dir, 0777, true);

    // minimal valid schema
    $schemaPath = $dir . '/schema.json';
    file_put_contents($schemaPath, json_encode([
        'db' => [
            'user' => true,
            'password' => true,
        ],
    ], JSON_THROW_ON_ERROR));

    // secrets file exists but will never be reached
    $secretsPath = $dir . '/secrets.json.enc';
    file_put_contents($secretsPath, 'irrelevant');

    $container = new Container();

    $config = Mockery::mock(SecretsConfigInterface::class);
    $config->shouldReceive('secretsFile')->andReturn($secretsPath);
    $config->shouldReceive('schemaFile')->andReturn($schemaPath);
    $config->shouldReceive('appKeyBase64')->andReturn('BASE64KEY');

    $container->addShared(SecretsConfigInterface::class, fn () => $config);

    $provider = new SecretsServiceProvider();
    $provider->register($container);

    expect(fn () => $container->get(SecretsInterface::class))
        ->toThrow(CryptoRuntimeException::class);
});





