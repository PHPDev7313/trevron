<?php

use JDS\Http\Middleware\ExtractRouteInfo;
use JDS\Http\MiddlewareResolver;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Http\RouteDispatcher;
use JDS\Routing\Route;
use Tests\Stubs\Fakes\FakeContainer;
use Tests\Stubs\Fakes\FakeController;
use Tests\Stubs\Fakes\FakeMiddleware;
use Tests\Stubs\Fakes\FakeMiddlewareTwo;

beforeEach(function () {

    // -------------------------------------------------
    // This is the test route used in pipeline
    // -------------------------------------------------
    $this->routes = [
        [
            'GET',
            '/test/{id}',
            [
                FakeController::class,
                'testAction',
                [FakeMiddleware::class],
                ['label' => 'Test', 'path' => '/', 'requires_token' => false],
            ]
        ]
    ];

    // -------------------------------------------------
    // Fake container for all needed classes
    // -------------------------------------------------
    $this->container = new FakeContainer([
        FakeController::class   => new FakeController(),
        FakeMiddleware::class   => new FakeMiddleware(),
        FakeMiddlewareTwo::class => new FakeMiddlewareTwo(),

        'config' => new class {
            public function get($key) {
                return ['metadata' => [], 'routes' => []];
            }
        },
    ]);

    // -------------------------------------------------
    // Correct constructor usage for MiddlewareResolver
    // -------------------------------------------------
    $this->resolver = new MiddlewareResolver(
        container: $this->container,
        globalMiddleware: [FakeMiddleware::class]
    );

    $this->router     = new Route();
    $this->dispatcher = new RouteDispatcher();
});


/**
 * 1. ExtractRouteInfo correctly identifies route handler + args
 */
it('extracts handler + args from route table', function () {

    $request = new Request([], [], [], [], [
        'REQUEST_URI'    => '/test/123',
        'REQUEST_METHOD' => 'GET'
    ]);

    $extractor = new ExtractRouteInfo(
        ['routes' => $this->routes, 'metadata' => []],
        '', '', ''
    );

    $extractor->process($request, new class implements JDS\Contracts\Middleware\RequestHandlerInterface {
        public function handle(Request $request): Response {
            return new Response("pass");
        }
    });

    expect($request->getRouteHandler())->toBe([FakeController::class, 'testAction']);
    expect($request->getRouteHandlerArgs())->toBe(['id' => '123']);
});













