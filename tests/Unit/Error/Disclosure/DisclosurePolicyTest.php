<?php

it('allows disclosure only when the correct key is present', function () {
    $_SERVER['JDS_DEBUG_KEY'] = 'correct';

    $policy = new \JDS\Error\Disclosure\KeyedDevelopmentDisclosurePolicy('correct');
    expect($policy->allowSensitiveDetails())->toBeTrue();

    $_SERVER['JDS_DEBUG_KEY'] = 'wrong';
    expect($policy->allowSensitiveDetails())->toBeFalse();
});








