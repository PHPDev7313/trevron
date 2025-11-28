<?php

use JDS\Json\JsonFileReader;

it('reads file contents successfully', function () {
    $reader = new JsonFileReader();

    $path = create_tmp_file(__DIR__ . '/json/', 'one.json', '{"a":1}');
    $result = $reader->read($path);

    expect($result['success'])->toBeTrue()
        ->and($result['data'])->toBe('{"a":1}');
});

it('returns error when file is missing', function () {
    $reader = new JsonFileReader();

    $path = __DIR__ . '/json/two.json';
    $result = $reader->read($path);

    expect($result['success'])->toBeFalse()
        ->and(str_contains($result['error'], 'File not found') !== false)->toBeTrue();
});

