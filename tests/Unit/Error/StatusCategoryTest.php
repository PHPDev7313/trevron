<?php

use JDS\Error\StatusCategory;

it('1. maps codes to categories correctly', function () {
    expect(StatusCategory::fromCode(500))->toBe(StatusCategory::Server);
    expect(StatusCategory::fromCode(2105))->toBe(StatusCategory::Containers);
    expect(StatusCategory::fromCode(2402))->toBe(StatusCategory::Entity);
    expect(StatusCategory::fromCode(3601))->toBe(StatusCategory::ConsoleKernel);
    expect(StatusCategory::fromCode(4108))->toBe(StatusCategory::Database);
    expect(StatusCategory::fromCode(4503))->toBe(StatusCategory::Image);
});

it('2. maps HttpKernel codes using the 200-range block', function () {
    expect(StatusCategory::fromCode(3950))
        ->toBe(StatusCategory::HttpKernel);
});

it('3. does not bleed into the next category range', function () {
    // 2400–2499 is Entity, 2500 starts Enum
    expect(StatusCategory::fromCode(2500))
        ->toBe(StatusCategory::Enum);
});

it('4. falls back to Server for unknown status codes', function () {
    expect(StatusCategory::fromCode(99999))
        ->toBe(StatusCategory::Server);
});












