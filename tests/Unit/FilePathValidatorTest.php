<?php

use JDS\FileSystem\FilePathValidator;

it('validates and creates directory for a path', function () {
    $validator = new FilePathValidator();

    $target = __DIR__ . '/sub/dir/file.json';

    $result = $validator->validate($target);

    expect($result['success'])->toBeTrue()
        ->and(is_dir(dirname($target)))->toBeTrue()
        ->and(isset($result['path']))->toBeTrue();
});

it('fails when path is empty', function () {
    $validator = new FilePathValidator();

    $result = $validator->validate('');

    expect($result['success'])->toBeFalse()
        ->and(isset($result['error']))->toBeTrue();
});

