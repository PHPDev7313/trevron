<?php

use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Routing\RouterInterface;
use JDS\Http\Middleware\RouterDispatch;
use JDS\Http\Request;
use JDS\Http\Response;
use Psr\Container\ContainerInterface;

beforeEach(function () {
    //
    // Create mocks for dependencies
    //
    $this->router = Mockery::mock(RouterInterface::class);
    $this->container = Mockery::mock(ContainerInterface::class);

    //
    // Required because RouterDispatch calls $requestHandler->handle($request)
    //
    $this->requestHandler = Mockery::mock(RequestHandlerInterface::class);

    $this->middleware = new RouterDispatch(
        router: $this->router,
        container: $this->container
    );
});

it('1. dispatches route and returns handler response', function () {
    $request = new Request([], [], [], [], []);

    //
    // router returns: handler + vars
    //
    $handler = fn () => new Response("OK", 200);

    $this->router->shouldReceive('dispatch')
        ->once()
        ->with($request, $this->container)
        ->andReturn([$handler, []]);

    //
    // middleware should *not* call the request handler afterwards
    //
    $this->requestHandler->shouldNotReceive('handle');

    $response = $this->middleware->process($request, $this->requestHandler);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getContent())->toBe("OK");
});

it('2. passes route variable into handler correctly', function() {
    $request = new Request([], [], [], [], []);

    $handler = function ($id, $slug) {
        return new Response("ID=$id SLUG=$slug");
    };

    $vars = ['id' => 55, 'slug' => 'hello-world'];

    $this->router->shouldReceive('dispatch')
        ->once()
        ->andReturn([$handler, $vars]);

    $response = $this->middleware->process($request, $this->requestHandler);

    expect($response->getContent())
        ->toBe("ID=55 SLUG=hello-world");
});

it('3. supports handlers defined as an array-style callable', function () {
    $request = new Request([], [], [], [], []);

    $controller = new class {
        public function show($name)
        {
            return new Response("Hello {$name}");
        }
    };

    $this->router->shouldReceive('dispatch')
        ->once()
        ->andReturn([[$controller, 'show'], ['name' => 'John']]);

    $response = $this->middleware->process($request, $this->requestHandler);

    expect($response->getContent())->toBe("Hello John");
});

it('4. handler must return a Response object', function () {
    $request = new Request([], [], [], [], []);

    $handler = fn () => "Not a response";

    $this->router->shouldReceive('dispatch')
        ->once()
        ->andReturn([$handler, []]);

    $this->requestHandler->shouldNotReceive('handle');

    expect(fn () => $this->middleware->process($request, $this->requestHandler))
        ->toThrow(TypeError::class); // call_user_func_array will cause a mismatch
});

it('5. errors thrown inside handler bubble up', function () {
    $request = new Request([], [], [], [], []);

    $handler = fn () => throw new RuntimeException("Boom!");

    $this->router->shouldReceive('dispatch')
        ->once()
        ->andReturn([$handler, []]);

    expect(fn () => $this->middleware->process($request, $this->requestHandler))
        ->toThrow(RuntimeException::class);
});

it('6. errors thrown by the router bubble up', function () {
    $request = new Request([], [], [], [], []);

    $this->router->shouldReceive('dispatch')
        ->once()
        ->andThrow(new RuntimeException("Router failed"));

    expect(fn () => $this->middleware->process($request, $this->requestHandler))
        ->toThrow(RuntimeException::class);
});








