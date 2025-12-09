<?php

use JDS\Error\ExceptionFormatter;
use JDS\Error\ExceptionHandler;

beforeEach(function () {
    //
    // Reset debug mode before each test
    //
    ExceptionHandler::disableDebug();
});

it('1. renders only the safe message in production mode', function () {
    ExceptionHandler::disableDebug();

    $safeMessage = "[500] Server Error: Something bad happened.";
    $exception = new RuntimeException('Internal failer');

    ob_start();
    ExceptionHandler::render($exception, $safeMessage);
    $output = trim(ob_get_clean());

    //
    // In production mode, NOTHING except safeMessage should appear
    //
    expect($output)->toBe($safeMessage)
        ->not->toContain("Exception:")
        ->not->toContain("Stack Trace")
        ->not->toContain($exception->getMessage());
});

it('2. renders the full formatted exception output in debug mode', function () {
    ExceptionHandler::enableDebug();

    $safeMessage = "[500] Server Error: Something bad happened.";
    $exception = new RuntimeException('Debug failure');

    ob_start();
    ExceptionHandler::render($exception, $safeMessage);
    $output = ob_get_clean();

    expect($output)->toContain($safeMessage)
        ->toContain("=== Exception Details ===")
        ->toContain("RuntimeException")
        ->toContain("Debug failure")
        ->toContain("=== Stack Trace ===");
});

it('3. includes formatted stack trace in debug mode', function () {
    ExceptionHandler::enableDebug();

    $exception = new RuntimeException('Trace test');
    $safeMessage = "[500] Server Error: Trace test.";

    //
    // Spy: get formatted trace so we can assert part of it appears
    //
    $formatted = ExceptionFormatter::format($exception);

    ob_start();
    ExceptionHandler::render($exception, $safeMessage);
    $output = ob_get_clean();

    //
    // Ensure some portion of formatted trace appears
    //
    $firstLineOfTrace = strtok($formatted, "\n"); // first line only
    expect($output)->toContain(trim($firstLineOfTrace));
});

it('4. renders chained exception correctly in debug mode', function () {
    ExceptionHandler::enableDebug();

    $root = new RuntimeException("Root cause");
    $wrapped = new RuntimeException("Outer exception", 0, $root);

    $safeMessage = "[500] Server Error: Chained exception test.";

    ob_start();
    ExceptionHandler::render($wrapped, $safeMessage);
    $output = ob_get_clean();

    //
    // Should display BOTH exceptions
    //
    expect($output)->toContain("Outer exception")
        ->toContain("Root cause");
});

it('5. never leaks the formatted trace when debug mode is off', function () {
    ExceptionHandler::disableDebug();

    $exception = new RuntimeException("Hidden message");
    $safeMessage = "[500] Server Error: Hidden test.";

    $formatted = ExceptionFormatter::format($exception);

    ob_start();
    ExceptionHandler::render($exception, $safeMessage);
    $output = ob_get_clean();

    //
    // Ensure the safe message appears
    //
    expect($output)->toContain($safeMessage);

    //
    // Ensure NONE of the formatted trace appears
    //
    $firstLineOfTrace = strtok($formatted, "\n");
    expect($output)->not->toContain($firstLineOfTrace);
});








