<?php

use JDS\Diff\Strategy\ObjectDiffStrategy;

class TestUser {
    public string $name = "John";
    public string $role = "user";
}

it('detect changes in object properties', function () {
    $strategy = new ObjectDiffStrategy();

    $before = new TestUser();
    $after = new TestUser();
    $after->role = 'Admin';

    $result = $strategy->diff($before, $after);

    expect($result['role']['changed'])->toBeTrue();
});


