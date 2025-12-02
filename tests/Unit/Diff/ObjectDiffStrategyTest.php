<?php
namespace Tests\Unit\Diff;

use JDS\Diff\Strategy\ObjectDiffStrategy;

class ObjectDiffStrategyTest {
    public string $name = "John";
    public string $role = "user";
}

it('detect changes in object properties', function () {
    $strategy = new ObjectDiffStrategy();

    $before = new ObjectDiffStrategyTest();
    $after = new ObjectDiffStrategyTest();
    $after->role = 'Admin';

    $result = $strategy->diff($before, $after);

    expect($result['role']['changed'])->toBeTrue();
});


