<?php

use JDS\FileSystem\JsonFileWriter;
use JDS\Json\JsonBuilder;
use JDS\Json\JsonEncoder;

it('saves json to disk via JsonBuilder', function () {
    $encoder = new JsonEncoder();
    $writer = new JsonFileWriter();
    $validator = new \JDS\FileSystem\FilePathValidator();
    $gen = new \JDS\FileSystem\FileNameGenerator();

    $builder = new JsonBuilder($encoder, $writer, $validator, $gen);

    $res = $builder->save(['x' => 'y'], __DIR__ . '/json', 'thing');

    expect($res['success'])->toBeTrue();

    // verify file exists in
    $files = glob(__DIR__ . '/json/*.json');

    expect(count($files))->toBeGreaterThanOrEqual(1);

    $content = json_decode(file_get_contents($files[4]), true);
    expect($content['x'])->toBe('y');
});


