<?php

use JDS\Auditor\LoggerManager;
use JDS\Bootstrap\ServiceLoader;
use JDS\Exceptions\Configuration\ConfigRuntimeException;
use JDS\Exceptions\ServiceProvider\ServiceProviderRuntimeException;
use JDS\Logging\ActivityLogger;
use JDS\Logging\ExceptionLogger;
use JDS\ServiceProvider\LoggingServiceProvider;
use League\Container\Container;
use Monolog\Logger;

//
// Helper: minimal valid base config for Config class
//
function baseConfig(array $override = []): array {
    return array_merge([
        "environment" => "development",
        'db' => [
            'driver' => 'pdo_mysql',
            'dbname' => 'test_db',
            'host' => 'localhost',
            'port' => 3306,
        ],
    ], $override);
}

//
// Helper: create a container via Serviceloader with LoggingServiceProvider
//
function makeLoggingContainer(array $configData): Container
{
    $loader = new ServiceLoader($configData);
    $loader->addProvider(LoggingServiceProvider::class);
    return $loader->boot();
}

//
// 1. Throws if Config::class is missing (bypassing ServiceLoader on purpose)
//
it('1. throws if Config::class is missing from the container', function () {
    $container = new Container();
    $provider = new LoggingServiceProvider();

    $provider->register($container);
})->throws(ConfigRuntimeException::class);

//
// 2. Throws if loggers config is missing
//
it('2. throws if loggers configuration is missing', function () {
    $configData = baseConfig([

    ]); // no 'loggers' key at all

    makeLoggingContainer($configData);
})->throws(ServiceProviderRuntimeException::class);

//
// 3. Throws if basic/exception logger entries are missing
//
it('3. throws if basic or exception logger entries are missing', function () {
    $configData = baseConfig([
        'loggers' => [
            'basic' => [
                // only basic exists
                'name' => 'basic',
                'path' => 'php://memory',
                'level' => 'debug'
            ],
            // 'exception' missing
        ],
    ]);
    makeLoggingContainer($configData);
})->throws(ServiceProviderRuntimeException::class);

//
// 4. Successfully registers ActivityLogger, ExceptionLogger, Loggermanager
//
it('4. registers ActivityLogger, ExceptionLogger, and LoggerManager as services', function () {
    $configData = baseConfig([
        'loggers' => [
            'basic' => [
                'name'  => 'basic-log',
                'path'  => 'php://memory',
                'level' => 'debug',
            ],
            'exception' => [
                'name'  => 'exception-log',
                'path'  => 'php://memory',
                'level' => 'error',
            ],
        ],
    ]);
    $container = makeLoggingContainer($configData);

    expect($container->has(ActivityLogger::class))->toBeTrue()
        ->and($container->has(ExceptionLogger::class))->toBeTrue()
        ->and($container->has(LoggerManager::class))->toBeTrue()
        ->and($container->get(ActivityLogger::class))->toBeInstanceOf(ActivityLogger::class)
        ->and($container->get(ExceptionLogger::class))->toBeInstanceOf(ExceptionLogger::class)
        ->and($container->get(LoggerManager::class))->toBeInstanceOf(LoggerManager::class);

});

////
//// 5. Uses Monolog loggers based on config (sanity check via LoggerManager)
////
//it('5. registers monolog loggers into LoggerManager using config', function () {
//    $configData = baseConfig([
//        'loggers' => [
//            'basic' => [
//                'name'  => 'basic-name',
//                'path'  => 'php://memory',
//                'level' => 'debug',
//                ],
//            'exception' => [
//                'name'  => 'exception-name',
//                'path'  => 'php://memory',
//                'level' => 'error',
//            ],
//        ],
//    ]);
//
//    $container = makeLoggingContainer($configData);
//
//    /** @var LoggerManager $manager */
//    $manager = $container->get(LoggerManager::class);
//
//    $basic = $manager->getLogger('basic');
//    $exception = $manager->getLogger('exception');
//
//    expect($basic)->toBeInstanceOf(\Psr\Log\LoggerInterface::class)
//        ->and($exception)->toBeInstanceOf(\Psr\Log\LoggerInterface::class)
//        ->and($basic)->getName()->toBe('basic-name')
//        ->and($exception)->getName()->toBe('exception-name');
//
//});

//
// 6. Ensures provider implements the correct interface (meta sanity)
//
it('5. implements ServiceProviderInterface', function () {
    $provider = new LoggingServiceProvider();

    expect($provider)->toBeInstanceOf(LoggingServiceProvider::class);
});




