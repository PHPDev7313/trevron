<?php

it('check to see if path is not valid when path is empty', function () {
    $path = new \JDS\FileSystem\FilePathValidator();
    $results = $path->validate('');

    expect($results['success'])->toBeFalse();
});

it('check to see if path is valid', function () {
    $path = new \JDS\FileSystem\FilePathValidator();
    $results = $path->validate('../../src/logs/framework_error.log');

    expect($results['success'])->toBeTrue()
        ->and($results['path'])->toBeString('../../src/logs/framework_error.log');
});




