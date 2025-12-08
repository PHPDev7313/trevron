<?php

namespace JDS\Http;

use Exception;
use JDS\Contracts\Session\SessionInterface;
use Throwable;

class Request
{
    private SessionInterface $session;
    private mixed $routeHandler;
    private array $routeHandlerArgs;
    private float $startTime = 0.0;

    /** @var array<string, mixed> */
    private array $attributes = [];

    public function __construct(
        public array $getParams, // $_GET
        public array $postParams = [], // $_POST
        public array $cookies = [], // $_COOKIE
        public array $files = [], // $_FILES
        public array $server = [] // $_SERVER
    )
    {
    }

    public static function createFromGlobals(): static
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    public function getPathInfo(): string
    {
        return strtok($this->server['REQUEST_URI'], '?');
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function getQueryString(): ?string
    {
        return $this->server['QUERY_STRING'] ?? null;
    }

    public function getFullUri(): string
    {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            ? 'https'
            : 'http';

        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri = $this->server['REQUEST_URI'] ?? '/';

        return "{$scheme}://{$host}{$uri}";
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    public function postInput($key): mixed
    {
        return $this->postParams[$key] ?? null;
    }

    public function getQueryParam(string $key, mixed $default=null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    public function getPostParam(string $key, mixed $default=null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    public function getRouteHandler(): mixed
    {
        return $this->routeHandler;
    }

    /**
     * @throws Exception
     */
    public function setRouteHandler(mixed $routeHandler): void
    {
        try {
            if (empty($routeHandler)) {
                throw new Exception('Route handler cannot be empty.');
            }
            $this->routeHandler = $routeHandler;
        } catch (Throwable $exception) {
            throw new Exception('Error setting route handler: ' . $exception->getMessage());
        }
    }

    public function getRouteHandlerArgs(): array
    {
        return $this->routeHandlerArgs;
    }

    /**
     * @throws Exception
     */
    public function setRouteHandlerArgs(array $routeHandlerArgs): void
    {
        $this->routeHandlerArgs = $routeHandlerArgs;
    }

    public function getServerVariable(string $serverVariable): ?string
    {
        return $this->server[$serverVariable] ?? null;
    }

    // ==================================
    //          Attribute System
    // ==================================


    public function withAttribute(string $name, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function getAttribute(string $name, mixed $default=null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setStartTime(float $time): void
    {
        $this->startTime = $time;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

}

