<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 29, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: SecretsCommands.v1.2.FINAL.md
*/

use JDS\Console\Command\Secrets\EncryptSecretsCommand;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\SecretsValidator;

beforeEach(function() {
    $this->tmpDir = sys_get_temp_dir() . '/jds_encrypt_' . uniqid();
    mkdir($this->tmpDir, 0777, true);

    $this->plainPath = $this->tmpDir . '/secrets.plain.json';
    $this->encPath = $this->tmpDir . '/secrets.json.enc';
    $this->schemaPath = $this->tmpDir . '/secrets.schema.json';

    // must be valid base64 for SecretsCrypto::fromBase64()
    $this->key = base64_encode(random_bytes(32));
});

afterEach(function () {
    foreach (glob($this->tmpDir . '/*') as $file) {
        unlink($file);
    }
    rmdir($this->tmpDir);
});

it('1. returns 1 if plaintext secrets file does not exist', function () {
    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath,
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('2. returns 1 if plaintext secrets json is invalid', function () {
    file_put_contents($this->plainPath, '{ invalid json');

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath,
    );

    $status = $command->execute();
    expect($status)->toBe(1);
});

it('3. returns 1 if validate is requested and schema file is missing', function () {
    file_put_contents($this->plainPath, json_encode(['foo' => 'bar']));

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath,
    );

    $status = $command->execute(['validate' => true]);

    expect($status)->toBe(1);
});

it('4. returns 1 if schema json is invalid', function () {
    file_put_contents($this->plainPath, json_encode(['foo' => 'bar']));
    file_put_contents($this->schemaPath, '{ broken schema');

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath,
    );

    $status = $command->execute(['validate' => true]);

    expect($status)->toBe(1);
});

it('5. encrypts secrets successfully and returns 0', function () {
    file_put_contents($this->plainPath, json_encode([
        'db' => ['user' => 'root', 'password' => 'secret'],
        ]));

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath,
    );

    $status = $command->execute();

    expect($status)->toBe(0);
    expect(file_exists($this->encPath))->toBeTrue();
});

it('6. encrypted file does not contain plaintext values', function () {
    file_put_contents($this->plainPath, json_encode([
        'jwt' => ['token' => 'super-secret-token'],
    ]));

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath
    );

    $command->execute();

    $encrypted = file_get_contents($this->encPath);

    expect($encrypted)->not->toContain('supper-secret-token');
    expect($encrypted)->not->toContain('jwt');
    expect($encrypted)->not->toContain('{');
});

it('7. validates against schema when validate flag is used', function () {
    file_put_contents($this->plainPath, json_encode([
        'db' => ['user' => 'root'],
    ]));

    file_put_contents($this->schemaPath, json_encode([
        'db' => [
            'user' => 'string',
            ],
      ]));

    $command = new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath
    );

    $status = $command->execute(['validate' => true]);

    expect($status)->toBe(0);
    expect(file_exists($this->encPath))->toBeTrue();
});

it('8. fails validation when a required secret key is missing', function () {
    $schema = [
        'db' => [
            'user' => '',
            'password' => '',
        ],
    ];

    $secrets = [
        'db' => [
            'user' => 'root',
            // password missing
        ],
    ];

    $validator = new SecretsValidator($schema);

    expect(fn () => $validator->validate($secrets))
        ->toThrow(
            CryptoRuntimeException::class,
            "Missing required secret: db.password"
        );
});

