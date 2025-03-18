<?php
require __DIR__ . '/../../vendor/autoload.php';

use JDS\Handlers\ExceptionFormatter;
use JDS\Http\StatusCodeManager;
use JDS\Logging\ExceptionLogger;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

// Test for production mode
it('logs messages correctly in production mode', function () {
    // Use the real result of StatusCodeManager::getMessage()
    $code = 500;
    $message = \JDS\Http\StatusCodeManager::getMessage($code); // Get the real message

    // Create a mock for Monolog\Logger and enforce LoggerInterface compatibility
    $mockLogger = Mockery::mock(Logger::class, LoggerInterface::class);
    $mockLogger
        ->shouldReceive('log')
        ->once()
        ->with(
            'error',
            $message . ' | Details: Processing unexpected data.' // Use the actual message returned
        );

    // Create ExceptionLogger using real StatusCodeManager class directly in the log method
    $exceptionLogger = new ExceptionLogger($mockLogger, new \JDS\Http\StatusCodeManager(), true);

    // Call the method we're testing
    $formattedMessage = $exceptionLogger->log($code, 'Processing unexpected data.', 'error');

    // Assert the result
    expect($formattedMessage)->toBe(
        $message . ' | Details: Processing unexpected data.'
    );
});

it('logs messages with exception trace in development mode', function () {
    // Set up a valid status code and use the real StatusCodeManager to fetch the message
    $code = 500;
    $message = \JDS\Http\StatusCodeManager::getMessage($code); // Retrieve the actual message

    // Create an exception for testing (simulate what happens during runtime)
    $exception = new \RuntimeException('Test Exception message');

    // Mock the logger
    $mockLogger = Mockery::mock(Logger::class, LoggerInterface::class);
    $mockLogger
        ->shouldReceive('log')
        ->once()
        ->with(
            'error', // Logging level
            $message . ' | Details: Processing unexpected data. | Exception: ' . ExceptionFormatter::formatTrace($exception)
        );

    // Create ExceptionLogger with mock logger and real StatusCodeManager
    $exceptionLogger = new ExceptionLogger($mockLogger, new \JDS\Http\StatusCodeManager(), false); // 'false' for development mode

    // Call the `log` method to log with an exception
    $formattedMessage = $exceptionLogger->log($code, 'Processing unexpected data.', 'error', $exception);

    // Assert the formatted message includes all expected parts
    expect($formattedMessage)->toBe(
        $message . ' | Details: Processing unexpected data. | Exception: ' . ExceptionFormatter::formatTrace($exception)
    );
});

afterEach(function () {
    Mockery::close();
});






