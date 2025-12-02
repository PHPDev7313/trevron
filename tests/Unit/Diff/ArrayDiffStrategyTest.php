<?php

use JDS\Diff\Strategy\ArrayDiffStrategy;

it('detetcs change in array fields', function () {
    $strategy = new ArrayDiffStrategy();

    $before = ['name' => 'John', 'role' => 'User'];
    $after = ['name' => 'John', 'role' => 'Admin'];

    $result = $strategy->diff($before, $after);

    expect($result['role']['changed'])->toBeTrue();
});


