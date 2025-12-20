<?php
declare(strict_types=1);





/*
 / ------------------------------------------------------------
 / Test Doubles
 / ------------------------------------------------------------
 */

use FastRoute\Dispatcher;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Exceptions\Http\HttpException;
use JDS\Exceptions\Http\HttpRequestMethodException;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\Navigation\NavigationMetadataCollection;
use JDS\Http\Request;
use JDS\Routing\ProcessRoutes;
use JDS\Routing\Route;
use JDS\Routing\RouteCollection;
use Tests\Contract\Stubs\Http\Routing\FakeDispatcher;
use Tests\Contract\Stubs\Http\Routing\NullNextHandler;

it('[v1.2 FINAL] ProcessRoutes separates routing and navigation metadata', function () {
    $processed = ProcessRoutes::process([
        [
            'GET',
            '/',
            [
                'HomeController',
                'index',
                [],
                [
                    'label' => 'Home',
                    'path' => null,
                    'requires_token' => false,
                ],
            ],
        ],
    ]);

    expect($processed->routes)->toBeInstanceOf(RouteCollection::class);
    expect($processed->metadata)->toBeInstanceOf(NavigationMetadataCollection::class);

    $routes = $processed->routes->all();
    $meta = $processed->metadata->all();

    expect($routes)->toHaveCount(1)
        ->and($routes[0])->toBeInstanceOf(Route::class)
        ->and($meta)->toHaveCount(1)
        ->and($meta[0])->toMatchArray([
            'label'          => 'Home',
            'path'           => null,
            'requires_token' => false,
        ]);

});

it('[v1.2 FINAL] invalid route metadata fails closed', function () {

    try {
        ProcessRoutes::process([
            [
                'GET',
                '/',
                [
                    'HomeController',
                    'index',
                    [],
                    [
                        'label'       => 'Home',
                        'INVALID_KEY' => true,
                    ],
                ],
            ],
        ]);

        fail('Expected StatusException was not thrown');

    } catch (StatusException $e) {
        expect($e->getStatusCodeEnum())->toBe(StatusCode::ROUTE_METADATA_INVALID);
    }
});

it('[v1.2 FINAL] ExtractRouteInfo attaches Route and params on match', function () {
    $route = new Route('GET', '/', ['Controller', 'method']);

    $dispatcher = new FakeDispatcher([
        Dispatcher::FOUND,
        $route,
        ['id' => '42'],
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);
    $request = new Request('GET', '/', '/', [], [], [], [], []);
    $next = new NullNextHandler();

    $response = $middleware->process($request, $next);

    expect($request->getRoute())->toBe($route);
    expect($request->getRouteParams())->toBe(['id' => '42']);
    expect($response->getStatusCode())->toBe(200);
});

it('[v1.2 FINAL] ExtractRouteInfo throws 404 on route not found', function () {
    $dispatcher = new FakeDispatcher([
        Dispatcher::NOT_FOUND,
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);
    $request = new Request('GET', '/missing', 'missing', [], [], [], [], []);

    $middleware->process($request, new NullNextHandler());
    
})->throws(HttpException::class);

it('[v1.2 FINAL] ExtractRouteInfo throws 405 on method mismatch', function () {

    $dispatcher = new FakeDispatcher([
        Dispatcher::METHOD_NOT_ALLOWED,
        ['GET'],
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);
    $request = new Request('POST', '/', '/', [], [], [], [], []);

    $middleware->process($request, new NullNextHandler());

})->throws(HttpRequestMethodException::class);



