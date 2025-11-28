<?php
// tests/Unit/JsonDecoderTest.php

it('decodes to object by default when assoc flag is false', function () {
    $decoder = new \JDS\Json\JsonDecoder();

    $json = json_encode(['a' => 1, 'b' => 2]);
    $result = $decoder->decode($json, false);

    expect($result['success'])->toBeTrue()
        ->and(is_object($result['data']))->toBeTrue()
        ->and($result['data']->a)->toBeNumeric(1);
});

it('decodes to associative array when assoc true', function () {
    $decoder = new \JDS\Json\JsonDecoder();

    $json = json_encode(['x' => 'y']);
    $result = $decoder->decode($json, true);

    expect($result['success'])->toBeTrue()
        ->and(is_array($result['data']))->toBeTrue()
        ->and($result['data']['x'])->toBe('y');
});

