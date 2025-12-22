<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

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

