<?php

use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir() . "\\jds_roundtrip_" . uniqid();
    mkdir($this->tmpDir, 0777, true);

    $this->plainPath    = $this->tmpDir . "\\secrets.plain.json";
    $this->encPath      = $this->tmpDir . "\\secrets.json.enc";

    $this->key = base64_encode(random_bytes(32));
});

afterEach(function () {
    foreach (glob($this->tmpDir . "\\*") as $file) {
        unlink($file);
    }
    rmdir($this->tmpDir);
});

it('1. encrypts and decrypt secrets with no data loss', function () {
    $original = [
        'db' => [
            'user' => 'root',
            'password' => 'super-secret',
        ],
        'jwt' => [
            'access' => 'access-token',
            'refresh' => 'refresh-token',
        ],
        'misc' => [
            'tokenTTL' => 3600,
            'algorithm' => 'HS256',
        ],
    ];

    // write plaintext
    file_put_contents(
        $this->plainPath,
        json_encode($original, JSON_PRETTY_PRINT)
    );

    // encrypt
    $encryptStatus = (new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath
    ))->execute();

    expect($encryptStatus)->toBe(0);
    expect(file_exists($this->encPath))->toBeTrue();

    // decrypt (capture output)
    ob_start();
    $decryptStatus = (new DecryptSecretsCommand(
        $this->key,
        $this->encPath
    ))->execute();
    $output = ob_get_clean();

    expect($decryptStatus)->toBe(0);

    $decoded = json_decode($output, true);
    expect($decoded)->toBe($original);
});







