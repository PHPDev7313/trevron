<?php

use JDS\Auditor\CentralizedLogger;
use JDS\Auditor\LoggerManager;
use Psr\Log\LoggerInterface;

it('can register a logger', function () {
    $manager = new LoggerManager();

    // create a mock logger
    $mockLogger = Mockery::mock(LoggerInterface::class);

    $manager->registerLogger('audit', $mockLogger);

    expect($manager->getLogger('audit'))
    ->toBeInstanceOf(CentralizedLogger::class);

});

it('can retrieve registered logger', function () {
    $manager = new LoggerManager();

    // create a mock logger
    $mockLogger = Mockery::mock(LoggerInterface::class);

    // register the logger
    $manager->registerLogger('audit', $mockLogger);

    // Retrieve the logger and verify it wraps the correct underlying logger
    $logger = $manager->getLogger('audit');
    expect($logger)->toBeInstanceOf(CentralizedLogger::class);

});

it('throws exception when retrieving an unregistered logger', function () {
    $manager = new LoggerManager();

    // assert that retrieving an unregistered logger throws an exception
    $manager->getLogger('nonexistent');

})->throws(\JDS\Auditor\Exception\LoggerNotFoundException::class, "Logger 'nonexistent' is not registered.");

it('overwrites logger if registered with the same name twice', function () {
    $manager = new LoggerManager();

    // mock two loggers
    $mockLogger1 = Mockery::mock(LoggerInterface::class);
    $mockLogger2 = Mockery::mock(LoggerInterface::class);

    // register the first logger and overwrite it
    $manager->registerLogger('audit', $mockLogger1);
    $manager->registerLogger('audit', $mockLogger2);

    $retrievedLogger = $manager->getLogger('audit');

  $wrappedLogger = $retrievedLogger->getLogger();

    // verify the second (last registered) logger is retrieved
    expect($wrappedLogger)->toBe($mockLogger2);
});

it('wraps loggers in CentralizedLogger when registered', function () {
    $manager = new LoggerManager();

    // mock the logger and register it
    $mockLogger = Mockery::mock(LoggerInterface::class);
    $manager->registerLogger('audit', $mockLogger);

    // retrieve the wrapper
    $centralizedLogger = $manager->getLogger('audit');

    // verify it wraps the mock logger

    expect($centralizedLogger)->toBeInstanceOf(CentralizedLogger::class);
});

it('allows logging via a registered logger', function () {
    $manager = new LoggerManager();

    // Mock the underlying logger
    $mockLogger = Mockery::mock(LoggerInterface::class);

    // Spy on the `log` method of the mock logger
    $mockLogger->shouldReceive('log')
        ->once() // Expect it to be called once
        ->with('INFO', 'USER LOGGED IN',
            Mockery::on(function ($context) {
                return $context['user'] = '123' && isset($context['timestamp']);
            }) // Expect specific arguments
        )
        ->andReturnNull();
    // Register the logger and retrieve it
    $manager->registerLogger('audit', $mockLogger);
    $logger = $manager->getLogger('audit');

    // Perform logging via the wrapper
    $logger->log('INFO', 'User logged in', ['user' => '123']);
});


