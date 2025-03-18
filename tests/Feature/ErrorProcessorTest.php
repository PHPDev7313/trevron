<?php

use JDS\Handlers\ExceptionFormatter;
use JDS\Handlers\ExceptionHandler;
use JDS\Http\StatusCodeManager;
use JDS\Logging\ExceptionLogger;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

// Test that process works after valid initialization.
it('calls process after initializing ErrorProcessor', function () {
    $environment = 'production';
    $container = new Container();
    $container->delegate(new ReflectionContainer());
    $container->add(LoggerInterface::class, Logger::class);
    $mockLogger = Mockery::mock(Logger::class, LoggerInterface::class);
    $container->add('logger', LoggerInterface::class)
        ->addArgument($mockLogger);

    


    ExceptionHandler::initializeWithEnvironment($environment);
    $code = 500;
    $mockLogger->shouldReceive('log')
        ->once()
        ->withArgs(function ($code, $userMessage, $level, $exception) {
            return $code === 500
                && $userMessage === 'A new error occurred.'
                && $level === 'error'
                && ExceptionFormatter::formatTrace($exception);
        })
        ->andReturn('Logged message');

    $statusCodeManager = new StatusCodeManager();
    $exceptionLogger = new ExceptionLogger($mockLogger, $statusCodeManager, true);
    ErrorProcessor::initialize($exceptionLogger);

    ErrorProcessor::process(new Exception('Test Exception'), $code, 'A new error occurred.', 'debug');

//    Mockery::close();
});

// Test that process throws an exception if not initialized.
it('throws an exception if ErrorProcessor is not initialized', function () {
    $code = 500;
    expect(fn() => ErrorProcessor::process(new Exception('Test Exception'), $code))
        ->toThrow(\JDS\Handlers\HandlerRuntimeException::class, "ErrorProcessor is not initialized.");
});

// Test that initialize fails with invalid logger type.
it('throws a TypeError if invalid logger is passed during initialization', function () {
    expect(fn() => ErrorProcessor::initialize('invalidLogger'))
        ->toThrow(\TypeError::class);
});




