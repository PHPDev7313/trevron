<?php

use JDS\Http\StatusCodeManager;
use JDS\Exceptions\Http\InvalidArgumentException;

it('1. returns a formatted message for a known status code', function () {
    $message = StatusCodeManager::getMessage(2402);

    expect($message)
        ->toBe('[2402] Entity Error: Entity not found');
});

it('2. returns an unknown message for an unknown status code', function () {
    $message = StatusCodeManager::getMessage(9999);

    expect($message)
        ->toBe('[9999] Unknown Status Code');
});

it('3. handles null status codes gracefully', function () {
    $message = StatusCodeManager::getMessage(null);

    expect($message)
        ->toBe('[null] Unknown Error! No Status Code Provided.');
});

it('4. can generate a status code from a category and offset', function () {
    $code = StatusCodeManager::make('Repository', 3);

    expect($code)->toBe(3103);
});

it('5. throws if an unknown category is used', function () {
    StatusCodeManager::make('UnknownCategory');
})->throws(InvalidArgumentException::class);

it('6. throws if a negative offset is used', function () {
    StatusCodeManager::make('Repository', -1);
})->throws(InvalidArgumentException::class);

it('7. validates known and unknown status codes', function () {
    expect(StatusCodeManager::isValidCode(2402))->toBeTrue();
    expect(StatusCodeManager::isValidCode(9999))->toBeFalse();
});

it('8. detects categories for standard 100-range blocks', function () {
    expect(StatusCodeManager::getCategoryForCode(2405))
        ->toBe('Entity');
});

it('9. detects categories for 200-range ConsoleKernel block', function () {
    expect(StatusCodeManager::getCategoryForCode(3700))
        ->toBe('ConsoleKernel');
});








