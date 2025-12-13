<?php

use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;

it('1. returns the correct default message', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->defaultMessage())
        ->toBe("Database Error: Migration apply failed")
        ->and(StatusCode::IMAGE_UPLOAD_FAILED->defaultMessage())
        ->toBe("Image Error: Upload failed");

});

it('2. resolves categories from codes', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->category())
        ->toBe(StatusCategory::Database);

    expect(StatusCode::CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED->category())
        ->toBe(StatusCategory::ConsoleKernel);
});

it('3. formats codes consistently', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->formatted())
        ->toBe('[4100] Database Error: Migration apply failed');
});

it('4. provides a default message for a status code', function () {
    $message = StatusCode::AUTHENTICATION_FAILED->defaultMessage();

    expect($message)
        ->toBeString()
        ->not->toBeEmpty();
});

it('5. returns the correct category for a status code', function () {
    expect(StatusCode::DATABASE_PDO_ERROR->category())
        ->toBe(StatusCategory::Database);
});

it('6. formats the status code and message correctly', function () {
    $formatted = StatusCode::JSON_ENCODING_FAILED->formatted();

    expect($formatted)
        ->toBe('[4400] JSON Error: Failed to encode data to JSON.');
});






