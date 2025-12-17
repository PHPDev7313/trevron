<?php

namespace JDS\Http;


use JDS\Routing\Route;
use JDS\Session\Session;

/**
 * Framework Request object for HTTP lifecycle
 *
 * NOT a PSR-7 object, but compatible in naming + structure.
 */
class Request
{
    private ?Route $route = null;

    /** @var array<string,string> */
    private array $routeParams = [];

    private float $startTime = 0.0;

    /** @var array<string,mixed> */
    private array $attributes = [];

    private ?Session $session = null;

    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly string $pathInfo,
        private readonly array $queryParams = [],
        private readonly array $postParams = [],
        private readonly array $cookies = [],
        private readonly array $server = [],
        private readonly array $files = []
    ) {
        $this->startTime = microtime(true);
    }

    // ============================================================
    // Basic Getters
    // ============================================================

    public static function createFromGlobals(): static
    {
        $server = $_SERVER;

        $method = $server['REQUEST_METHOD'] ?? 'GET';

        $uri = $server['REQUEST_URI'] ?? '/';

        //
        // Strip query string from URI to get pathInfo
        //
        $pathInfo = parse_url($uri, PHP_URL_PATH) ?? '/';

        // -------------------------------------------------------------
        // STRIP ROUTE PATH PREFIX (deloyment concern)
        // -------------------------------------------------------------
        $routePath = trim($_ENV['ROUTE_PATH'] ?? '');

        if ($routePath !== '') {
            $prefix = '/' . trim($routePath, '/');

            if ($pathInfo === $prefix ||
                str_starts_with($pathInfo, $prefix . '/')
            ) {
                $pathInfo = substr($pathInfo, strlen($prefix));
                $pathInfo = $pathInfo === '' ? '/' : $pathInfo;
            }
        }

        return new static(
            strtoupper($method),
            $uri,
            $pathInfo,
            $_GET,
            $_POST,
            $_COOKIE,
            $server,
            $_FILES,
        );
    }

    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPathInfo(): string
    {
        return $this->pathInfo;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getPostParams(): array
    {
        return $this->postParams;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getServerParams(): array
    {
        return $this->server;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    // ============================================================
    // Route Object
    // ============================================================


    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function hasRoute(): bool
    {
        return $this->route !== null;
    }

    public function withRoute(Route $route): self
    {
        $clone = clone $this;
        $clone->route = $route;
        return $clone;
    }

    // ============================================================
    // Route Parameters (FastRoute vars)
    // ============================================================

    /**
     * @param array<string,string> $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * @return array<string,string>
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function hasRouteParam(string $key): bool
    {
        return array_key_exists($key, $this->routeParams);
    }

    // ============================================================
    // Request Attributes (middleware or system-level)
    // ============================================================

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    // ============================================================
    // Session (optional)
    // ============================================================

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function hasSession(): bool
    {
        return $this->session !== null;
    }

    // ============================================================
    // Timing / Profiling
    // ============================================================

    public function setStartTime(float $time): void
    {
        $this->startTime = $time;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    public function expectsJson(): bool
    {
        //
        // 1. Explicit Accept header
        //
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        //
        // 2. XHR / Fetch request
        //
        $requestdWith = $this->server['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strtolower($requestdWith) === 'xmlhttprequest') {
            return true;
        }

        //
        // 3. Explicit atrtribute set by middleware or route
        //
        if ($this->getAttribute('expects_json') === true) {
            return true;
        }

        return false;
    }
}

