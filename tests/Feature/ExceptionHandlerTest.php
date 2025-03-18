<?php

use JDS\Handlers\ExceptionHandler;
use League\Container\Container;

it('renders production-friendly errors if environment is production', function () {
    $container = new Container();
    $container->add('environment', 'production');
    ExceptionHandler::initializeWithEnvironment($container->get('environment'));
    ob_start();

    // act: call the render method
    ExceptionHandler::render(new Exception('Test exception'), 'A production-friendly error occurred.');
    $output = ob_get_clean();

    // assert: production-friendly message is displayed (no debug info)
    expect($output)
        ->toContain('[Error]: A production-friendly error occurred.')
        ->not->toContain('Test exception');
});

it('renders detailed error in development environment', function () {
    // arrange: create the DI container and set the environment
    $container = new Container();
    $container->add('environment', 'development');

    // initialize ExceptionHandler with the mock environment
    ExceptionHandler::initializeWithEnvironment($container->get('environment'));

    // capture output
    ob_start();

    // act: call the render method with an exception
    ExceptionHandler::render(new Exception('Development exception'), 'A development-friendly error occurred.');

    $output = ob_get_clean();

    // assert: detailed error message is displayed
    expect($output)
        ->toContain('Development exception')
        ->toContain('A development-friendly error occurred.')
        ->toContain('Trace');
});








