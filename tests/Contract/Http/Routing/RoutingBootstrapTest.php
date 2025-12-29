<?php

use FastRoute\Dispatcher;
use JDS\Bootstrap\RoutingBootstrap;
use JDS\Exceptions\Bootstrap\BootstrapInvariantViolationException;
use JDS\Http\RouteDispatcher;
use JDS\Routing\ProcessRoutes;
use JDS\Routing\RouteBootstrap;

it('1. builds a dispatcher during bootstrap', function () {
    $routes = ProcessRoutes::process([
        ['GET', '/', [fn () => null]],
    ]);

    $initial = RouteBootstrap::buildDispatcher($routes);

    $bootstrap = new RoutingBootstrap($routes, $initial);

    $dispatcher = $bootstrap->bootstrap();

    expect($dispatcher)->toBeInstanceOf(Dispatcher::class);
});

it('2. prevents dispatcher access before routing is locked', function () {
    $routes = ProcessRoutes::process([
        ['GET', '/', [fn () => null]],
    ]);

    $initial = RouteBootstrap::buildDispatcher($routes);
    $bootstrap = new RoutingBootstrap($routes, $initial);

    $bootstrap->bootstrap();

    expect(fn () => $bootstrap->dispatcher())
        ->toThrow(BootstrapInvariantViolationException::class);
});

it('3. allows dispatcher access after routing is locked', function () {
    $routes = ProcessRoutes::process([
        ['GET', '/', [fn () => null]],
    ]);

    $initial = RouteBootstrap::buildDispatcher($routes);
    $bootstrap = new RoutingBootstrap($routes, $initial);

    $bootstrap->bootstrap();
    $bootstrap->lock();

    expect($bootstrap->dispatcher())
        ->toBeInstanceOf(Dispatcher::class);
});

it('4. prevents routing bootstrap from running twice', function () {
    $routes = ProcessRoutes::process([
        ['GET', '/', [fn () => null]],
    ]);

    $initial = RouteBootstrap::buildDispatcher($routes);
    $bootstrap = new RoutingBootstrap($routes, $initial);

    $bootstrap->bootstrap();
    $bootstrap->lock();

    expect(fn () => $bootstrap->bootstrap())
        ->toThrow(BootstrapInvariantViolationException::class);
});



