<?php

use JDS\Diff\DiffEngine;
use JDS\Diff\Strategy\ScalarDiffStrategy;

it('executes the correct strategy', function () {
    $engine = new DiffEngine();
    $engine->addStrategy('scalar', new ScalarDiffStrategy());

    $result = $engine->diff('a', 'b', 'scalar');

    expect($result['value']['changed'])->toBeTrue();
});





