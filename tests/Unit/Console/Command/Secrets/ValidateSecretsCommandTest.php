<?php

use JDS\Console\Command\Secrets\ValidateSecretCommand;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir() . "\\jds_validate_" . uniqid();
    mkdir($this->tmpDir, 0777, true);

    $this->plainPath = $this->tmpDir . "\\secrets.plain.json";
    $this->schemaPath = $this->tmpDir . "\\secrets.schema.json";
});

afterEach(function () {
    foreach (glob($this->tmpDir . "\\*") as $file) {
        unlink($file);
    }
    rmdir($this->tmpDir);
});

it('1. returns 1 when plaintext secrets file is missing', function () {
    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath,
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('2. returns 1 when schema file is missing', function () {
    file_put_contents($this->plainPath, json_encode(['db' => []]));

    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('3. returns 1 when plaintext secrets json is invalid', function () {
    file_put_contents($this->plainPath, '{ invalid json');
    file_put_contents($this->plainPath, json_encode(['db' => []]));

    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('4. returns 1 when schema json is invalid', function () {
    file_put_contents($this->plainPath, json_encode(['db' => []]));
    file_put_contents($this->schemaPath, '{ invalid schema');

    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('5. returns 1 when required secret key is missing', function () {
    file_put_contents($this->plainPath, json_encode([
        'db' => ['user' => 'root'],
    ]));

    file_put_contents($this->schemaPath, json_encode([
        'db' => [
            'user' => '',
            'password' => '',
        ],
    ]));

    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    $status = $command->execute();

    expect($status)->toBe(1);
});

it('6. returns 0 when secrets satify the required structure', function () {
    file_put_contents($this->plainPath, json_encode([
        'db' => [
            'user' => 'root',
            'password' => 'secret',
        ],
        'jwt' => [
            'access' => 'token',
        ],
    ]));

    file_put_contents($this->schemaPath, json_encode([
        'db' => [
            'user' => '',
            'password' => '',
        ],
        'jwt' => [
            'access' => '',
        ],
    ]));

    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    ob_start();
    $status = $command->execute();
    $output = ob_get_clean();

    expect($status)->toBe(0);
    expect($output)->toContain("Secrets validated successfully");
});

it('7. returns 0 and prints help without validating when help flag is used', function () {
    $command = new ValidateSecretCommand(
        $this->schemaPath,
        $this->plainPath
    );

    ob_start();
    $status = $command->execute(['help' => true]);
    $output = ob_get_clean();

    expect($status)->toBe(0);
    expect($output)->toContain("secrets:validate");
});




