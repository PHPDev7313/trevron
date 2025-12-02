<?php

use JDS\Diff\Strategy\ScalarDiffStrategy;

it('detects change in scalar values', function () {
    $strategy = new ScalarDiffStrategy();
    $result = $strategy->diff('a', 'b');

    expect($result['value']['changed'])->toBeTrue();
});

