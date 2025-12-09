<?php


use JDS\Error\ExceptionLogger;
use JDS\Error\StatusCode;
use Psr\Log\LoggerInterface;

it('logs using status code and details', function () {
    $psrLogger = Mockery::mock(LoggerInterface::class);
    $exceptionLogger = new ExceptionLogger($psrLogger, false);

    $statusCode = StatusCode::DATABASE_MIGRATION_APPLY_FAILED;

    $psrLogger->shouldReceive('log')
        ->once()
        ->with(
            'error',
            '[4100] Database Error: Migration apply failed | Details: Something went wrong',
            Mockery::on(fn (array $ctx) => isset($ctx['exception']))
        );

    $loggedMessage = $exceptionLogger->log(
        $statusCode,
        'Something went wrong',
        'error',
        new RuntimeException('Test exception')
    );

    expect($loggedMessage)->toBe('[4100] Database Error: Migration apply failed | Details: Something went wrong');
});

it('does not include formatted trace in logs', function () {
    $psr = Mockery::mock(LoggerInterface::class);
    $logger = new ExceptionLogger($psr, false);

    $exception = new RuntimeException('Boom');

    $psr->shouldReceive('log')
        ->once()
        ->withArgs(function ($level, $message, $context) use ($exception) {
            return isset($context['exception'])
                && !isset($context['formatted_trace']);
        });

    $logger->log(
        StatusCode::SERVER_INTERNAL_ERROR,
        'test',
        'error',
        $exception
    );
});






