<?php


use JDS\Error\Disclosure\ProductionDisclosurePolicy;
use JDS\Error\ErrorContext;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;

it('strips exception and debug when disclosure is not allowed', function () {
    $ctx = new ErrorContext(
        httpStatus: 500,
        statusCode: StatusCode::SERVER_INTERNAL_ERROR,
        category: StatusCategory::Server,
        publicMessage: 'Internal Server Error',
        exception: new RuntimeException('secret'),
        debug: ['trace' => 'sensitive']
    );

    $sanitizer = new ErrorSanitizer(new ProductionDisclosurePolicy());
    $safe = $sanitizer->sanitize($ctx);

    expect($safe->exception)->toBeNull();
    expect($safe->debug)->toBe([]);
});