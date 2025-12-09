<?php

use JDS\Http\MiddlewareResolver;
use JDS\Http\Request;
use Psr\Container\ContainerInterface;
use Tests\Stubs\AnotherMiddleware;
use Tests\Stubs\DummyMiddleware;

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
    // Instance under test — global middleware only
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










