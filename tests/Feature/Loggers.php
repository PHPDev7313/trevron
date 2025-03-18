<?php

use JDS\Handlers\ExceptionFormatter;
use JDS\Http\StatusCodeManager;

require __DIR__ . '/../../vendor/autoload.php';

$container = new \League\Container\Container();
$container->delegate(new \League\Container\ReflectionContainer());

// setup the logger
$logger = new \Monolog\Logger('test');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/logs/test.log', \Monolog\Level::Debug));

$logger->info('test the logger');

// add the logger to the LoggerInterface
// this allows for different loggers that use the psr\log\loggerInterface to interchange here
$container->add(\Psr\Log\LoggerInterface::class, $logger);

// allow me to call the logger with an alias of 'logger'
$container->add('logger', $container->get(\Psr\Log\LoggerInterface::class));

// gives me a class that uses static methods and no constructor
// this has 3 different return types,
//  1. [code] message
//  2. [code] Unknown Error Code
//  3. Unknown Error! No Status Code Provided
$container->add(StatusCodeManager::class);

// gives me access to a static method to format Exceptions how I want them and returns it as a string (does not use a constructor)
// this can be easily mocked because it receives a Throwable $e in the static method and returns a string
$container->add(ExceptionFormatter::class);


