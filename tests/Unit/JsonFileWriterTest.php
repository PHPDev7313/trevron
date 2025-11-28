<?php


use JDS\FileSystem\JsonFileWriter;

it('writes json content to disk', function () {
    $writer = new JsonFileWriter();
//    dd(__DIR__ . "/json/test.json");
    $path = __DIR__ . "/json/test.json";
    $json = '{"ok": true}';

    $result = $writer->write($path, $json);

    expect($result['success'])->toBeTrue()
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toBe($json);
});

it('returns false and error when path invalid (directory cannot be created)', function () {
    $writer = new JsonFileWriter();

    // attempt to write into an invalid path (simulate by using a filename with \0)
    $path = "json/\0_invalid.json";
    $result = $writer->write($path, '{"x":1}');

    expect($result['success'])->toBeFalse()
        ->and(isset($result['error']))->toBeTrue();
});


