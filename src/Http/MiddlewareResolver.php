<?php

namespace JDS\Http;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Resloves the full middleware stack for a request:
 *
 *      - Global middleware (always applied)
 *      - Route-specific middleware (from ExtractRouteInfo)
 *
 * Output: ordered array of *instance* of MiddlewareInterface.
 */
final class MiddlewareResolver implements RequestHandlerInterface
{

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $globalMiddleware = [] // [ MiddlewareClass::class, ... ]
    ) {}

    /**
     * Kernel calls this via ->resolveMiddlewareList($request)
     */
    public function getMiddlewareForRequest(Request $request): array
    {
        try {
            //
            // 1. Start with global middlware
            //
            $resolved = $this->resolveList($this->globalMiddleware);

            //
            // 2. Add route-specific middleware (if any)
            //
            $routeSpecific = $request->getAttribute('route.middleware', []);

            if (!empty($routeSpecific)) {
                $resolved = array_merge(
                    $resolved,
                    $this->resolveList($routeSpecific)
                );
            }

            return $resolved;

        } catch (Throwable $e) {
            throw new StatusException(
                StatusCode::HTTP_PIPELINE_FAILURE,
                "MiddlewareResolver failed to resolve middleware list. [Middleware:Resolver].",
                $e
            );
        }
    }

    /**
     * Resolve a list of middleware class names into instantiated objects.
     *
     * @param array $list Class names
     * @return MiddlewareInterface[]
     */
    private function resolveList(array $list): array
    {
        $resolved = [];

        foreach ($list as $className) {

            //
            // 1. Validate class existence
            //
            if (!class_exists($className)) {
                throw new StatusException(
                    StatusCode::HTTP_PIPELINE_FAILURE,
                    "Middleware class does not exist: {$className}. [Middleware:Resolver]."
                );
            }

            //
            // 2. Resolve from container
            //
            try {
                $instance = $this->container->get($className);
            } catch (Throwable $e) {
                throw new StatusException(
                    StatusCode::HTTP_PIPELINE_FAILURE,
                    "Class {$className} is not a valid MiddlewareInterface implementation. [Middleware:Resolver]."
                );
            }

            //
            // 3. instance must implement MiddlewareInterface
            //
            if (!($instance instanceof MiddlewareInterface)) {
                throw new StatusException(
                    StatusCode::HTTP_PIPELINE_FAILURE,
                    "Class {$className} is not a valid MiddlewareInterface implementation. [Middleware:Resolver]."
                );
            }

            $resolved[] = $instance;
        }

        return $resolved;
    }

    /**
     * Legacy compatibility - Kernel calls handle() ONLY if someone misuses this class.
     * This ensures we always produce a meaningful error instead of silently failing.
     */
    public function handle(Request $request): Response
    {
        throw new StatusException(
            StatusCode::HTTP_KERNEL_GENERAL_FAILURE,
            "MiddlewareResolver cannot handle request directly. Use getMiddlewareForRequest(). [Middleware:Resolver]."
        );
    }
}

