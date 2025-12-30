<?php

use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir() . "\\jds_decript_" . uniqid();

    @mkdir($this->tmpDir, 0777, true);

    $this->plainPath = $this->tmpDir . "\\secrets.plain.json";
    $this->encPath = $this->tmpDir . "\\secrets.json.enc";

    // must be valid base64 for SecretsCrypto::fromBase64()
    $this->key = base64_encode(random_bytes(32));
    $this->badKey = base64_encode(random_bytes(32));
});

afterEach(function () {
    foreach (glob($this->tmpDir . "\\*") as $file) {
        unlink($file);
    }
    @rmdir($this->tmpDir);
});

it('1. returns 1 if encrypted secrets file does not exist', function () {
    $command = new DecryptSecretsCommand(
        $this->key,
        $this->encPath,
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('2. prints decrypted secrets as valid json and returns 0', function () {
    $original = [
        'db' => [
            'user' => 'root',
            'password' => 'secret',
        ],
        'jwt' => [
            'access' => 'token',
        ],
    ];

    // encrypt first (using real crypto)
    file_put_contents($this->plainPath, json_encode($original));

    (new EncryptSecretsCommand(
        $this->key,
        $this->plainPath,
        $this->encPath
    ))->execute();

    $command = new DecryptSecretsCommand(
        $this->key,
        $this->encPath
    );

    ob_start();
    $status = $command->execute();
    $output = ob_get_clean();

    expect($status)->toBe(0);


    $decoded = json_decode($output, true);
    expect($decoded)->toBe($original);
});







