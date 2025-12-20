<?php

namespace JDS\Routing;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Routing\RouteMiddlewareAwareInterface;


class Route implements RouteMiddlewareAwareInterface
{
    public function __construct(
        private string $method,
        private string $path,
        private array $handler,
        /** @var class-string<MiddlewareInterface>[] */
        private array $middleware = []
    )
    {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): array
    {
        return $this->handler;
    }

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Fluent API for adding middleware.
     */
    public function middleware(string ...$middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
}

