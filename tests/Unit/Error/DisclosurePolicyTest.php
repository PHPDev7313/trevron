<?php

use JDS\Error\Disclosure\KeyedDevelopmentDisclosurePolicy;

it('allows sensitive details only with correct key', function () {
    $_SERVER['JDS_DEBUG_KEY'] = 'abc';

    $policy = new KeyedDevelopmentDisclosurePolicy(expectedKey: 'abc');
    expect($policy->allowSensitiveDetails())->toBeTrue();

    $policy2 = new KeyedDevelopmentDisclosurePolicy(expectedKey: 'nope');
    expect($policy2->allowSensitiveDetails())->toBeFalse();
});



