<?php

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\FileSystem\DirectoryInitializationState;
use JDS\FileSystem\DirectoryStructureInitializer;


beforeEach(function () {
    $this->baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jds_test_struct_' . uniqid();
    mkdir($this->baseDir, 0755, true);

    $this->stateFile = $this->baseDir . '/state.json';
    $this->state = new DirectoryInitializationState($this->stateFile);
    $this->initializer = new DirectoryStructureInitializer($this->state);
});

afterEach(function () {
    if (is_dir($this->baseDir)) {
        exec(sprintf('rd -rf %s', escapeshellarg($this->baseDir)));
    }
});

it('1. creates full directory structure', function () {
    $structure = [
        'config',
        'logs',
        'templates' => [
            'admin',
            'user',
        ],
    ];

    $this->initializer->initialize($this->baseDir, $structure);

    expect(is_dir($this->baseDir . '/config'))->toBeTrue();
    expect(is_dir($this->baseDir . '/logs'))->toBeTrue();
    expect(is_dir($this->baseDir . '/templates/admin'))->toBeTrue();
    expect(is_dir($this->baseDir . '/templates/user'))->toBeTrue();
});

it('2. throws FILESYSTEM_INVALID_STRUCTURE on invalid nested structure', function () {
    $structure = [
        ['bad']
    ];

    try {
        $this->initializer->initialize($this->baseDir, $structure);
        test()->fail("Expected FILESYSTEM_INVALID_STRUCTURE not thrown.");
    } catch (StatusException $e) {
        expect($e->getCode())->toBe(StatusCode::FILESYSTEM_INVALID_STRUCTURE->value);
    }

});

//it('3. throws FILESYSTEM_DIRECTORY_CREATION_FAILED when mkdir fails', function () {
//    chmod($this->baseDir, 0555); // block creation
//
//    try {
//        $this->initializer->initialize($this->baseDir, ['config']);
//        test()->fail("Expected FILESYSTEM_DIRECTORY_CREATION_FAILED not thrown.");
//    } catch (StatusException $e) {
//        expect($e->getCode())->toBe(StatusCode::FILESYSTEM_DIRECTORY_CREATION_FAILED->value);
//    }
//});
//
//it('4. throws FILESYSTEM_DIRECTORY_NOT_WRITABLE when directory exists but unwritable', function () {
//    $dir = $this->baseDir . '/config';
//    mkdir($dir, 0555);
//
//    try {
//        $this->initializer->initialize($this->baseDir, ['config']);
//        test()->fail("Expected FILESYSTEM_DIRECTORY_NOT_WRITABLE not thrown.");
//    } catch (StatusException $e) {
//        expect($e->getCode())->toBe(StatusCode::FILESYSTEM_DIRECTORY_NOT_WRITABLE->value);
//    }
//});


it('5. initializer runs only once', function () {
    $this->initializer->initialize($this->baseDir, ['config']);

    // Make directory unwritable so second run would fail *if it ran*
    chmod($this->baseDir, 0555);

    // Should NOT throw because state file says already initialized
    $this->initializer->initialize($this->baseDir, ['config']);

    expect(true)->toBeTrue(); // reached safely
});













