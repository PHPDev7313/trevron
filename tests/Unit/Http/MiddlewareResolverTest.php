<?php

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\MiddlewareResolver;
use JDS\Http\Request;
use Psr\Container\ContainerInterface;
use Tests\Stubs\Http\AnotherMiddleware;
use Tests\Stubs\Http\DummyMiddleware;
use Tests\Stubs\Http\NotMiddleware;

beforeEach(function () {
    //
    // Simple test container (anonymous class)
    //
    $this->container = new class implements ContainerInterface {
        private array $bindings = [];

        public function bind(string $id, mixed $concrete): void
        {
            $this->bindings[$id] = $concrete;
        }

        public function get(string $id)
        {
            if (!isset($this->bindings[$id])) {
                throw new RuntimeException("Not found: {$id}");
            }
            return $this->bindings[$id];
        }

        public function has(string $id): bool
        {
            return isset($this->bindings[$id]);
        }
    };

    //
    // Register valid middleware in the container
    //
    $this->container->bind(DummyMiddleware::class, new DummyMiddleware());
    $this->container->bind(AnotherMiddleware::class, new AnotherMiddleware());

    //
    // Instance under test - global middleware only
    //
    $this->resolver = new MiddlewareResolver(
        $this->container,
        globalMiddleware: [
            DummyMiddleware::class,
        ]
    );
});

it('1. resolves only global middleware when route has none', function () {

    $request = new Request([], [], [], []);

    $list = $this->resolver->getMiddlewareForRequest($request);

    expect($list)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(DummyMiddleware::class);
});

it('2. resolves global + route.specific middleware in merge order', function () {
    $request = (new Request([], [], [], []))
        ->withAttribute('route.middleware', [
            AnotherMiddleware::class
        ]);

    $list = $this->resolver->getMiddlewareForRequest($request);

    expect($list)->toHaveCount(2);

    expect($list[0])->toBeInstanceOf(DummyMiddleware::class);
    expect($list[1])->toBeInstanceOf(AnotherMiddleware::class);
});

it('3. throws if middleware class does not exist', function () {
    $resolver = new MiddlewareResolver(
        $this->container,
        globalMiddleware: [
            'NonExistentClass',
        ]
    );

    expect(fn () => $resolver->getMiddlewareForRequest(new Request([], [], [], [], [])))
        ->toThrow(StatusException::class)
        ->and(fn ($e) => expect($e->getCode())->toBe(StatusCode::HTTP_PIPELINE_FAILURE->value));
});

it('4. throws if class exists but is NOT a MiddlewareInterface', function () {
    $this->container->bind(NotMiddleware::class, new NotMiddleware());

    $resolver = new MiddlewareResolver(
        $this->container,
        globalMiddleware: [
            NotMiddleware::class,
        ]
    );

    expect(fn () => $resolver->getMiddlewareForRequest(new Request([])))
        ->toThrow(StatusException::class)
        ->and(fn ($e) => expect($e->getCode())->toBe(StatusCode::HTTP_PIPELINE_FAILURE->value));
});

it('5. throws when container fails to instantiate middleware', function () {
    // Bind a broken factory into the container
    $this->container->bind('BrokenMiddleware', null);

    $resolver = new MiddlewareResolver(
        $this->container,
        globalMiddleware: [
            'BrokenMiddleware',
        ]
    );

    expect(fn () => $resolver->getMiddlewareForRequest(new Request([])))
        ->toThrow(StatusException::class)
        ->and(fn ($e) => expect($e->getCode())->toBe(StatusCode::HTTP_PIPELINE_FAILURE->value));
});

it('6. handle() must always throw - this class is not a request handler', function () {
    expect(fn () => $this->resolver->handle(new Request([], [], [], [], [])))
        ->toThrow(StatusException::class)
        ->and(fn ($e) => expect($e->getCode())->toBe(StatusCode::HTTP_KERNEL_GENERAL_FAILURE->value));
});

