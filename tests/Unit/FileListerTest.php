<?php

use JDS\FileSystem\FileLister;

it('lists json files in directory', function () {
    $lister = new FileLister();

    create_tmp_file(__DIR__ . '/json', 'a.json', '{}');
    create_tmp_file(__DIR__ . '/json', 'b.json', '{}');

    $result = $lister->list(__DIR__ . '/json');

    expect($result['success'])->toBeTrue()
        ->and(count($result['files']))->toBeGreaterThanOrEqual(2);
});

it('fails when directory does not exist', function () {
    $lister = new FileLister();

    $result = $lister->list(__DIR__ . '/not-found');

    expect($result['success'])->toBeFalse();
});


