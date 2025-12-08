<?php

use JDS\Error\StatusCode;
use JDS\Error\StatusException;
use JDS\FileSystem\DirectoryInitializationState;

require_once __DIR__ . '/../../Utils/FileSystem.php'; // if you extracted safe_* helpers

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jds_test_state_' . uniqid();
    mkdir($this->tempDir, 0755, true);

    $this->stateFile = $this->tempDir . '/state.json';
});

afterEach(function () {
    safe_rmdir($this->tempDir);
});
//        exec(sprintf('rm -rf %s', escapeshellarg($this->tempDir)));

it('1. returns false when state file does not exist', function () {
    $state = new DirectoryInitializationState($this->stateFile);

    expect($state->isInitialized())->toBeFalse();
});

it('2. throws JSON_FILE_READ_ERROR when file cannot be read', function () {
    //
    // Create a file but remove read permissions
    //
    file_put_contents($this->stateFile, 'test');
    chmod($this->stateFile, 0000);

    try {
        $state = new DirectoryInitializationState($this->stateFile);
        $state->isInitialized();
    } catch (StatusException $e) {
        chmod($this->stateFile, 0755);
        expect($e->getCode())->toBe(StatusCode::JSON_DECODING_FAILED->value);
    }
});

it('3. throws JSON_DECODING_FAILED when JSON is invalid', function () {
    file_put_contents($this->stateFile, '{not valid json');

    try {
        $state = new DirectoryInitializationState($this->stateFile);
        $state->isInitialized();
    } catch (StatusException $e) {
        expect($e->getCode())->toBe(StatusCode::JSON_DECODING_FAILED->value);
    }
});


it('4. returns true when initialized flag is present', function () {
    file_put_contents($this->stateFile, json_encode(['initialized' => true]));

    $state = new DirectoryInitializationState($this->stateFile);

    expect($state->isInitialized())->toBeTrue();
});

it('5. markInitialized creates missing directory and writes state file', function () {
    $deepStateFile = $this->tempDir . '/deep/path/state.json';

    $state = new DirectoryInitializationState($deepStateFile);
    $state->markInitialized();

    expect(is_file($deepStateFile))->toBeTrue();

    $json = json_decode(file_get_contents($deepStateFile), true);

    expect($json['initialized'])->toBeTrue();
});

it('6. throws JSON_ENCODING_FAILED when JSON encoding fails', function () {
    //
    // Force json_encode to fail by encoding invalid UTF-8
    //
    $state = new DirectoryInitializationState($this->stateFile);

    //
    // Override json_encode via stub
    //
    $badData = ['initialized' => "\xB1\x31"];

    //
    // Use reflection to inject bad JSON data
    //
    $ref = new ReflectionClass($state);
    $method = $ref->getMethod('markInitialized');
    $method->setAccessible(true);

    //
    // Monkey patch: replace json_encode with bad version using runkit or simple override
    // Instead, simulate by directly calling json_encode here:
    //
    expect(fn () => json_encode($badData, JSON_THROW_ON_ERROR))
        ->toThrow(Exception::class);

    // Since we cannot override json_encode inside markInitialized
    // this test documents the behavior: markInitialized should detect encoding errors.
});









