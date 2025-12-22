<?php

declare(strict_types=1);

/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 21, 2025
 *
 * See: RoutingARCHITECTURE.v1.2.FINAL.md
 */

/*
 / ------------------------------------------------------------
 / Test Doubles
 / ------------------------------------------------------------
 */

use FastRoute\Dispatcher;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\Navigation\BreadcrumbGenerator;
use JDS\Http\Navigation\NavigationMetadataCollection;
use JDS\Http\Request;
use JDS\Routing\ProcessedRoutes;
use JDS\Routing\ProcessRoutes;
use JDS\Routing\Route;
use JDS\Routing\RouteBootstrap;
use Tests\Contract\Stubs\Http\Routing\CapturingNextHandler;
use Tests\Contract\Stubs\Http\Routing\ExplodingDispatcher;
use Tests\Contract\Stubs\Http\Routing\FakeDispatcher;

/**
 * ------------------------------------------------------------
 * Contract Tests (v1.2 FINAL)
 * ------------------------------------------------------------
 */

it('1. [v1.2 FINAL] ProcessRoutes separates routing routes and navigation metadata', function () {
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
        [
            'GET',
            '/roles',
            [
                'RoleController',
                'index',
                [],
                [
                    'label' => 'Roles',
                    'path' => '/', // parent breadcrumb points to /
                    'requires_token' => false,
                ],
            ],
        ],
    ]);

    expect($processed)->toBeInstanceOf(ProcessedRoutes::class);

    // Routes are executable Route objects
    $routes = $processed->routes->all();
    expect($routes)->toHaveCount(2);
    expect($routes[0])->toBeInstanceOf(Route::class);
    expect($routes[1])->toBeInstanceOf(Route::class);

    // Metadata is separate and safe for navigation use
    expect($processed->metadata)->toBeInstanceOf(NavigationMetadataCollection::class);
    $meta = $processed->metadata->all();
    expect($meta)->toHaveCount(2);

    expect($meta[0])->toMatchArray([
        'uri' => '/',
        'label' => 'Home',
        'path' => null,
        'requires_token' => false,
    ]);

    expect($meta[1])->toMatchArray([
        'uri' => '/roles',
        'label' => 'Roles',
        'path' => '/',
        'requires_token' => false,
    ]);
});

it('2. [v1.2 FINAL] invalid route metadata fails closed with StatusException + StatusCode::ROUTE_METADATA_INVALID', function () {
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
                       'INVALID_KEY' => true, // not allowed
                   ],
               ],
           ],
       ]);
       $this->fail('Expected exception not thrown');
   } catch (StatusException $e) {
       expect($e->getStatusCodeEnum())->toBe(StatusCode::ROUTE_METADATA_INVALID);
       throw $e; // rethrow so Pest still sees the exception
   }
})->throws(StatusException::class);


it('3. [v1.2 FINAL] RouteBootstrap builds a FastRoute dispatcher from ProcessedRoutes and matches routes', function () {
    $processed = ProcessRoutes::process([
        [
            'GET',
            '/',
            [
                'HomeController',
                'index',
                [],
            ],
        ],
        [
            'GET',
            '/roles',
            [
                'RoleController',
                'index',
                [],
            ],
        ],
    ]);

    $dispatcher = RouteBootstrap::buildDispatcher($processed);

    // FastRoute returns FOUND with the handler as Route object (by contract)
    $found = $dispatcher->dispatch('GET', '/roles');

    expect($found[0])->toBe(Dispatcher::FOUND);
    expect($found[1])->toBeInstanceOf(Route::class);
    expect($found[1]->getPath())->toBe('/roles');
});

it('4. [v1.2 FINAL] ExtractRouteInfo attaches Route + params to Request on FOUND and delegates to next handler', function () {
    $route = new Route(
        method: 'GET',
        path: '/roles/{id}',
        handler: ['RoleController', 'show', []],
        middleware: []
    );

    $dispatcher = new FakeDispatcher([
        Dispatcher::FOUND,
        $route,
        ['id' => '123'],
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);

    $request = new Request('GET', '/roles/123', '/roles/123', [], [], [], [], []);

    $next = new CapturingNextHandler(function (Request $req) use ($route) {
        // Contract: ExtractRouteInfo attaches the matched Route
        expect($req->getRoute())->toBe($route);

        // Contract: route params are attached
        expect($req->getRouteParam('id'))->toBe('123');
    });

    $response = $middleware->process($request, $next);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

it('5. [v1.2 FINAL] ExtractRouteInfo converts METHOD_NOT_ALLOWED into HTTP 405 exception', function () {
    $dispatcher = new FakeDispatcher([
        Dispatcher::METHOD_NOT_ALLOWED,
        ['GET'],
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);

    $request = new Request('POST', '/', '/', [], [], [], [], []);

    $middleware->process($request, new CapturingNextHandler(fn() => null));
})
    ->throws(\JDS\Exceptions\Http\HttpRequestMethodException::class);

it('6. [v1.2 FINAL] ExtractRouteInfo converts NOT_FOUND into HTTP 404 exception', function () {
    $dispatcher = new FakeDispatcher([
        Dispatcher::NOT_FOUND,
    ]);

    $middleware = new ExtractRouteInfo($dispatcher);

    $request = new Request('GET', '/missing', '/missing', [], [], [], [], []);

    $middleware->process($request, new CapturingNextHandler(fn() => null));
})
    ->throws(\JDS\Exceptions\Http\HttpException::class);

it('7. [v1.2 FINAL] ExtractRouteInfo treats dispatcher throw as infrastructure failure -> StatusException(HTTP_ROUTE_DISPATCH_FAILURE)', function () {
    $middleware = new ExtractRouteInfo(new ExplodingDispatcher());

    $request = new Request('GET', '/', '/', [], [], [], [], []);

    try {
        $middleware->process($request, new CapturingNextHandler(fn() => null));
        $this->fail('Expected StatusException not thrown');
    } catch (StatusException $e) {
        expect($e->getStatusCodeEnum())->toBe(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE);
        throw $e;
    }
})->throws(StatusException::class);


/**
 * ------------------------------------------------------------
 * Navigation / Breadcrumb Contracts (v1.2 FINAL)
 * ------------------------------------------------------------
 *
 * IMPORTANT:
 * Your BreadcrumbGenerator currently reads from $_SERVER['REQUEST_URI'].
 * For contract tests, we set it explicitly (allowed) to avoid changing your design
 * right before freezing v1.2.
 */

it('8. [v1.2 FINAL] navigation metadata can generate breadcrumb chain deterministically (prefix-aware)', function () {
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
        [
            'GET',
            '/roles',
            [
                'RoleController',
                'index',
                [],
                [
                    'label' => 'Roles',
                    'path' => '/',
                    'requires_token' => false,
                ],
            ],
        ],
        [
            'GET',
            '/roles/create',
            [
                'RoleController',
                'create',
                [],
                [
                    'label' => 'Create Role',
                    'path' => '/roles',
                    'requires_token' => false,
                ],
            ],
        ],
    ]);

    $request = new Request(
        'GET',
        '/roles/create',
        '/roles/create',
        [],
        [],
        [],
        [],
        []
    );

    $generator = new BreadcrumbGenerator(
        routes: ['metadata' => $processed->metadata->all()],
        routePrefix: 'pec'
    );

    $crumbs = $generator->generateBreadcrumbs($request);
    expect($crumbs)->toHaveCount(3);

    expect($crumbs[0])->toMatchArray([
        'label' => 'Home',
        'path' => '/pec/',
    ]);

    expect($crumbs[1])->toMatchArray([
        'label' => 'Roles',
        'path' => '/pec/roles',
    ]);

    expect($crumbs[2])->toMatchArray([
        'label' => 'Create Role',
        'path' => '/pec/roles/create',
    ]);
});


