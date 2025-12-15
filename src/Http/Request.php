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
}




//private SessionInterface $session;
//    private mixed $routeHandler;
//    private array $routeHandlerArgs;
//    private float $startTime = 0.0;
//
//    /** @var array<string, mixed> */
//    private array $attributes = [];
//
//    private ?Route $route = null;
//
//    public function __construct(
//        public array $getParams, // $_GET
//        public array $postParams = [], // $_POST
//        public array $cookies = [], // $_COOKIE
//        public array $files = [], // $_FILES
//        public array $server = [] // $_SERVER
//    )
//    {
//    }
//
//    public static function createFromGlobals(): static
//    {
//        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
//    }
//
//    public function getPathInfo(): string
//    {
//        return strtok($this->server['REQUEST_URI'], '?');
//    }
//
//    public function getUri(): string
//    {
//        return $this->server['REQUEST_URI'] ?? '/';
//    }
//
//    public function getQueryString(): ?string
//    {
//        return $this->server['QUERY_STRING'] ?? null;
//    }
//
//    public function getFullUri(): string
//    {
//        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
//            ? 'https'
//            : 'http';
//
//        $host = $this->server['HTTP_HOST'] ?? 'localhost';
//        $uri = $this->server['REQUEST_URI'] ?? '/';
//
//        return "{$scheme}://{$host}{$uri}";
//    }
//
//    public function getMethod(): string
//    {
//        return $this->server['REQUEST_METHOD'];
//    }
//
//    public function getSession(): SessionInterface
//    {
//        return $this->session;
//    }
//
//    public function setSession(SessionInterface $session): void
//    {
//        $this->session = $session;
//    }
//
//    public function postInput($key): mixed
//    {
//        return $this->postParams[$key] ?? null;
//    }
//
//    public function getQueryParam(string $key, mixed $default=null): mixed
//    {
//        return $this->getParams[$key] ?? $default;
//    }
//
//    public function getPostParam(string $key, mixed $default=null): mixed
//    {
//        return $this->getParams[$key] ?? $default;
//    }
//
//    public function getRouteHandler(): mixed
//    {
//        return $this->routeHandler;
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function setRouteHandler(mixed $routeHandler): void
//    {
//        try {
//            if (empty($routeHandler)) {
//                throw new Exception('Route handler cannot be empty.');
//            }
//            $this->routeHandler = $routeHandler;
//        } catch (Throwable $exception) {
//            throw new Exception('Error setting route handler: ' . $exception->getMessage());
//        }
//    }
//
//    public function getRouteHandlerArgs(): array
//    {
//        return $this->routeHandlerArgs;
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function setRouteHandlerArgs(array $routeHandlerArgs): void
//    {
//        $this->routeHandlerArgs = $routeHandlerArgs;
//    }
//
//    public function getServerVariable(string $serverVariable): ?string
//    {
//        return $this->server[$serverVariable] ?? null;
//    }
//
//    // ==================================
//    //          Attribute System
//    // ==================================
//
//
//    public function withAttribute(string $name, mixed $value): self
//    {
//        $clone = clone $this;
//        $clone->attributes[$name] = $value;
//        return $clone;
//    }
//
//    public function getAttribute(string $name, mixed $default=null): mixed
//    {
//        return $this->attributes[$name] ?? $default;
//    }
//
//    public function getAttributes(): array
//    {
//        return $this->attributes;
//    }
//
//    public function setStartTime(float $time): void
//    {
//        $this->startTime = $time;
//    }
//
//    public function getStartTime(): float
//    {
//        return $this->startTime;
//    }
//
//    public function setRoute(Route $route): void
//    {
//        $this->route = $route;
//    }
//
//    public function getRoute(): ?Route
//    {
//        return $this->route;
//    }
//
//