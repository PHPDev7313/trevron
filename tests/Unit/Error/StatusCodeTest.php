<?php

use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;

it('returns the correct default message', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->defaultMessage())
        ->toBe("Database Error: Migration apply failed")
        ->and(StatusCode::IMAGE_UPLOAD_FAILED->defaultMessage())
        ->toBe("Image Error: Upload failed");

});

it('resolves categories from codes', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->category())
        ->toBe(StatusCategory::Database);

    expect(StatusCode::CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED->category())
        ->toBe(StatusCategory::ConsoleKernel);
});

it('formats codes consistently', function () {
    expect(StatusCode::DATABASE_MIGRATION_APPLY_FAILED->formatted())
        ->toBe('[4100] Database Error: Migration apply failed');
});







