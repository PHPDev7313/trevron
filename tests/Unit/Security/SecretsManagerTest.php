<?php

use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

it('saves and loads encrypted secrets correctly', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $tempFile = tempnam(sys_get_temp_dir(), 'secret');
    $manager = new SecretsManager($tempFile, $crypto);

    $testData = [
        'db' => ['user' => 'test', 'password' => 'pass'],
        'jwt' => ['access' => 'abc123'],
    ];

    // SAVE

    $manager->save($testData);

    expect(file_exists($tempFile))->toBeTrue();

    // LOAD
    $loaded = $manager->load();

    expect($loaded)->toBe($testData);
});

it('throws when the encrypted secrets file does not exist', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $fakeFile = sys_get_temp_dir() . '/nope-' . uniqid();
    $manager = new SecretsManager($fakeFile, $crypto);

    $manager->load(); // expect failure
})->throws(CryptoRuntimeException::class);

it('throws when encrypted file contains invalid ciphertext', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $tempFile = tempnam(sys_get_temp_dir(), 'secret');
    file_put_contents($tempFile, 'invalid-base64');

    $manager = new SecretsManager($tempFile, $crypto);

    $manager->load(); // expect failure
})->throws(CryptoRuntimeException::class);

it('throws when decrypted JSON is invalid', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $tempFile = tempnam(sys_get_temp_dir(), 'secret');

    // write valid encrypted garbage that can decrypt but not parse JSON
    $badJson = 'not json';
    $encrypted = $crypto->encryptString($badJson);

    file_put_contents($tempFile, $encrypted);

    $manager = new SecretsManager($tempFile, $crypto);

    $manager->load(); // expect failure
})->throws(CryptoRuntimeException::class);









