<?php

use JDS\Error\StatusCategory;

it('maps codes to categories correctly', function () {
    expect(StatusCategory::fromCode(500))->toBe(StatusCategory::Server);
    expect(StatusCategory::fromCode(2105))->toBe(StatusCategory::Containers);
    expect(StatusCategory::fromCode(2402))->toBe(StatusCategory::Entity);
    expect(StatusCategory::fromCode(3601))->toBe(StatusCategory::ConsoleKernel);
    expect(StatusCategory::fromCode(4108))->toBe(StatusCategory::Database);
    expect(StatusCategory::fromCode(4503))->toBe(StatusCategory::Image);
});












