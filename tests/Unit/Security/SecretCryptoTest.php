<?php

use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\SecretsCrypto;

it('encrypts and decrypts a string successfully', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $plaintext = 'sensitive data';
    $encrypted = $crypto->encryptString($plaintext);
    $decrypted = $crypto->decryptString($encrypted);

    expect($decrypted)->toBe($plaintext);
});

it('produces different ciphertext with each encryption', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $plaintext = 'same message';
    $c1 = $crypto->encryptString($plaintext);
    $c2 = $crypto->encryptString($plaintext);

    expect($c1)->not->toBe($c2);
});

it('fails to decrypt with the wrong key', function () {
    $key1 = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $key2 = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

    $crypto1 = new SecretsCrypto($key1);
    $crypto2 = new SecretsCrypto($key2);

    $plaintext = 'sensitive!';
    $encrypted = $crypto1->encryptString($plaintext);

   $crypto2->decryptString($encrypted); // should throw
})->throws(CryptoRuntimeException::class);

it('throws an exception on invalid base64 input', function () {
    $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $crypto = new SecretsCrypto($key);

    $crypto->decryptString('!! not based !!'); // invalid ciphertext
})->throws(CryptoRuntimeException::class);

