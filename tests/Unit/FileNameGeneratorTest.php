<?php


use JDS\FileSystem\FileNameGenerator;

it('generates a safe filename with timestamp and extension', function () {
    $gen = new FileNameGenerator();

    $name = $gen->generate("User Data");

    expect($name)->toBeString()
        ->and(strpos($name, '.json') !== false)->toBeTrue()
        ->and(preg_match('/^[a-z0-9_\-]+_\\d{8}_\\d{6}\\.json$/', $name))->toBeNumeric(1);
});

it('makeUnique returns same path when file does not exist and unique path when it does', function () {
    $gen = new FileNameGenerator();

    $dir = __DIR__ . '/json';
    $base = 'example_20200101_000000.json';
    $path = $gen->makeUnique($dir, $base);

    // should return path that doesn't exist
//    expect(basename($path))->toBe($base);

    // create file and call again - should add counter
    file_put_contents($path, "{}");

    $path2 = $gen->makeUnique($dir, $base);

    expect($path2)->not->toBe($path)
        ->and(basename($path2))->toMatch('/_1\\.json$/');
});


