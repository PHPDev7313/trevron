<?php

use JDS\Error\Disclosure\KeyedDevelopmentDisclosurePolicy;
use JDS\Error\Disclosure\ProductionDisclosurePolicy;
use JDS\Error\ErrorContext;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;

it('1. strips exception and debug data when disclosure is not allowed', function () {
    $context = new ErrorContext(
        httpStatus: 500,
        statusCode: StatusCode::SERVER_INTERNAL_ERROR,
        category: StatusCategory::Server,
        publicMessage: 'Internal Server Error',
        exception: new RuntimeException('secret'),
        debug: ['trace' => 'sensitive']
    );

    $sanitizer = new ErrorSanitizer(
        new ProductionDisclosurePolicy()
    );

    $sanitized = $sanitizer->sanitize($context);

    expect($sanitized->exception)->toBeNull()
        ->and($sanitized->debug)->toBe([]);
});

it('2. preserves exception and degug data when disclosure is allowed', function () {
    $_SERVER['JDS_DEBUG_KEY'] = 'dev-secret';

    $policy = new KeyedDevelopmentDisclosurePolicy('dev-secret');
    $sanitizer = new ErrorSanitizer($policy);

    $exception = new RuntimeException('boom');

    $context = new ErrorContext(
        httpStatus: 500,
        statusCode: StatusCode::SERVER_INTERNAL_ERROR,
        category: StatusCategory::Server,
        publicMessage: 'Internal Server Error',
        exception: $exception,
        debug: ['foo' => 'bar']
    );

    $sanitized = $sanitizer->sanitize($context);

    expect($sanitized->exception)->toBe($exception);
    expect($sanitized->debug)->toBe(['foo' => 'bar']);
});





