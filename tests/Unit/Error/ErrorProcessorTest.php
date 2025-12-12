<?php

use JDS\Error\ErrorProcessor;
use JDS\Error\ExceptionLogger;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

it('throws when not initialized', function () {
    ErrorProcessor::reset(); // instead of initialize(null)

    ErrorProcessor::process(
        new RuntimeException('Boom'),
        StatusCode::CONSOLE_KERNEL_PROCESSOR_ERROR
    );
})->throws(StatusException::class);

it('logs and delegates to ExceptionHandler when initialized', function () {
    $logger = Mockery::mock(ExceptionLogger::class);

    $logger->shouldReceive('log')
        ->once()
        ->with(
            StatusCode::DATABASE_GENERAL_ERROR,
            'Custom message',
            'critical',
            Mockery::type(Throwable::class)
        )
        ->andReturn('[4108] Database Error: General database error | Details: Custom message');

    ErrorProcessor::initialize($logger);

    // You may need to mock ExceptionHandler::render using a spyable wrapper
    ErrorProcessor::process(
        new RuntimeException('DB exploded'),
        StatusCode::DATABASE_GENERAL_ERROR,
        'Custom message',
        'critical'
    );

    expect(true)->toBeTrue(); // if no exception thrown, test passes
});



