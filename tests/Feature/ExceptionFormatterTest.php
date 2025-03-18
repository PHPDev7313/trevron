<?php

use JDS\Handlers\ExceptionFormatter;

it('formats the trace of an exception into a readable string', function () {
    $exception = new Exception("Test exception");
    $formattedTrace = ExceptionFormatter::formatTrace($exception);

    // Assertions depend on the exception's actual trace.
    // At the very least, ensure it contains some expected structure.
    expect($formattedTrace)->toContain('#0'); // Should contain the first trace index
    expect($formattedTrace)->toBeString();   // The output should be a string
});

