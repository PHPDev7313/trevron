<?php

use JDS\Json\JsonSorter;

it('sorts files oldest to newest and newest to oldest', function () {
    $sorter = new JsonSorter();

    $f1 = create_tmp_file(__DIR__ . '/json', 'a.json', '{"i":1}');
    sleep(1);
    $f2 = create_tmp_file(__DIR__ . '/json', 'b.json', '{"i":2}');

    $files = [$f1, $f2];
    $oldest = $sorter->sortByOldest($files);
    expect($oldest[0])->toBe($f1);
    $newest = $sorter->sortNewest($files);
    expect($newest[0])->toBe($f2);
});



