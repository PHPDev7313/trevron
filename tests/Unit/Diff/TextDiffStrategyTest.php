<?php

use JDS\Diff\Strategy\TextDiffStrategy;

it('detects changed lines in text', function () {
    $strategy = new TextDiffStrategy();

    $before = "Line A\nLine B\nLine C";
    $after = "Line A\nLine X\nLine C";

    $result = $strategy->diff($before, $after);

    expect($result['lines'])->toHaveKey(2)
        ->and($result['lines'][2]['after'])->toBe('Line X');
});





