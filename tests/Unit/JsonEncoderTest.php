<?php


use JDS\Json\JsonEncoder;

it('encodes simple array to json successfully', function () {
    $encoder = new JsonEncoder();

    $result = $encoder->encode(['name' => 'Alice', 'id' => 1]);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and(json_decode($result['json'], true)['name'])->toBeString('Alice')
        ->and(json_decode($result['json'], true)['id'])->toBeNumeric(1);
});

it('returns error when encoding unsupported data (resource)', function () {
    $encoder = new JsonEncoder();

    $resource = tmpfile();
    $result = $encoder->encode($resource);

    fclose($resource);

    expect($result['success'])->toBeFalse();
    expect(isset($result['error']))->toBeTrue();
});


