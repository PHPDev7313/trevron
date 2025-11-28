<?php

use JDS\Json\JsonDecoder;
use JDS\Json\JsonFileReader;
use JDS\Json\JsonLoader;
use JDS\Json\JsonSorter;

it('loads all json files into objects in oldest->newest order', function () {
    $lister = new \JDS\FileSystem\FileLister();
    $sorter = new JsonSorter();
    $reader = new JsonFileReader();
    $decoder = new JsonDecoder();
    $validator = new \JDS\FileSystem\FilePathValidator();

    $loader = new JsonLoader($lister, $sorter, $reader, $decoder, $validator);

    // create two files
    $p1 = create_tmp_file(__DIR__ . '/json', 'one.json', json_encode(["v" => 1]));
    sleep(1);
    $p2 = create_tmp_file(__DIR__ . '/json', 'two.json', json_encode(["v" => 2]));

    $res = $loader->loadAll(__DIR__ . '/json', false);

    expect($res['success'])->toBeTrue();
    $data = $res['data'];
    expect(count($data))->toBeGreaterThanOrEqual(2);
    expect($data[1]->v)->toBe(1);
    expect($data[2]->v)->toBe(2);
});

it('loads associative arrays when assoc=true', function () {
    $lister = new \JDS\FileSystem\FileLister();
    $sorter = new JsonSorter();
    $reader = new JsonFileReader();
    $decoder = new JsonDecoder();
    $validator = new \JDS\FileSystem\FilePathValidator();

    $loader = new JsonLoader($lister, $sorter, $reader, $decoder, $validator);

    create_tmp_file(__DIR__ . '/json', 'a.json', json_encode(["k" => "v"]));

    $res = $loader->loadAll(__DIR__ . '/json', true);

    expect($res['success'])->toBeTrue();
    expect(is_array($res['data'][0]))->toBeTrue();
    expect($res['data'][1]["k"])->toBe("v");
});


