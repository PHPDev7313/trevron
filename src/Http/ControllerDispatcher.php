<?php

namespace JDS\Http;

use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Contracts\Rendering\RendererInterface;
use JDS\Controller\AbstractController;
use JDS\Exceptions\Controller\ControllerInvocationException;
use JDS\Exceptions\Controller\ControllerMethodNotFoundException;
use JDS\Exceptions\Controller\ControllerNotFoundException;
use JDS\Routing\Route;
use JDS\Transformers\TransformerManager;
use JDS\Validation\MethodParameterValidator;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

final class ControllerDispatcher implements ControllerDispatcherInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MethodParameterValidator $validator,
        private readonly TransformerManager $transformerManager
    ) {}

    public function dispatch(Request $request): Response
    {
        $route = $request->getRoute();

        if (!$route instanceof Route) {
            throw new ControllerInvocationException(
                "Cannot dispatch controller because no Route is attached to the Request."
            );
        }
        $handler = $route->getHandler();

        // v1.2 FINAL: handler must be [ControllerClass::class, 'method']
        if (!is_array($handler) || count($handler) < 2 || !is_string($handler[0]) || !is_string($handler[1])) {
            throw new ControllerInvocationException(
                "Invalid route handler shape. Expected [controllerClass, method]. Got: " . gettype($handler)
            );
        }

        [$controllerClass, $method] = [$handler[0], $handler[1]];

        // ---------------------------------------------
        // 1. Instantiate Controller (DO NOT gate on has())
        // ---------------------------------------------
        try {
            $controller = $this->container->get($controllerClass);
        } catch (Throwable $e) {
            throw new ControllerNotFoundException(
                "Controller '{$controllerClass}' resolution failed: {$e->getMessage()}. [Controller:Dispatcher].", previous: $e
            );
        }
        // ------------------------------------------
        // 2. Inject Request + Container for legacy controllers
        // ------------------------------------------
        if ($controller instanceof AbstractController) {
            $controller->setRequest($request);
            $controller->setContainer($this->container);
        }

        // -------------------------------------------
        // 3. Ensure method exists
        // -------------------------------------------
        if (!method_exists($controller, $method)) {
            throw new ControllerMethodNotFoundException(
                "Method '{$method}' does not exist on controller '{$controllerClass}'. [Controller:Dispatcher]."
            );
        }

        $refMethod = new ReflectionMethod($controller, $method);

        // ----------------------------------------------------------------
        // 4. Resolve arguments (request injection, casting, transformation, validation)
        // ----------------------------------------------------------------
        $args = $this->resolveArguments($refMethod, $request);

        try {
            $result = $refMethod->invokeArgs($controller, $args);
        } catch (Throwable $e) {
            throw new ControllerInvocationException(
                "Error invoking controller '{$controllerClass}@{$method}': " . $e->getMessage() . ". [Controller:Dispatcher].",
                previous: $e
            );
        }

        return $this->normalizeResult($result);
    }

    // ======================================================
    // Argument Resolution Pipeline
    // ======================================================

    private function resolveArguments(ReflectionMethod $method, Request $request): array
    {
        $params    = $method->getParameters();
        $routeArgs = $request->getRouteParams();
        $resolved  = [];

        foreach ($params as $param) {

            $name = $param->getName();
            $type = $param->getType();

            //
            // ==== 1. Inject Request automatically ====
            //
            if ($type instanceof ReflectionNamedType && $type->getName() === Request::class) {
                $resolved[] = $request;
                continue;
            }

            //
            // ==== 2. Retrieve route param ====
            //
            if (!array_key_exists($name, $routeArgs)) {

                // Missing param → default available?
                if ($param->isDefaultValueAvailable()) {
                    $resolved[] = $param->getDefaultValue();
                    continue;
                }

                // No parameter → fail hard
                throw new ControllerInvocationException(
                    "Cannot resolve argument '{$name}' for controller method '{$method->getName()}'. [Controller:Dispatcher]."
                );
            }

            $rawValue = $routeArgs[$name];

            //
            // ==== 3. Typed parameter? ====
            //
            if ($type instanceof ReflectionNamedType) {

                $targetType = $type->getName();

                //
                // ---- a) Builtin scalar type: cast + validate ----
                //
                if ($type->isBuiltin()) {
                    $casted = $this->castValue($rawValue, $type, $name);

                    // validation applies after cast
                    $this->validator->validateParameter($param, $casted);

                    $resolved[] = $casted;
                    continue;
                }

                //
                // ---- b) Non-builtin type: try transformer ----
                //
                if ($this->transformerManager->supports($targetType)) {

                    $object = $this->transformerManager->transform($rawValue, $targetType);

                    // validate transformed object
                    $this->validator->validateParameter($param, $object);

                    $resolved[] = $object;
                    continue;
                }

                //
                // ---- c) Untransformable object type → fail ----
                //
                throw new ControllerInvocationException(
                    "Cannot resolve argument '{$name}'. No transformer for type '{$targetType}'. [Controller:Dispatcher]."
                );
            }

            //
            // ==== 4. No typehint → raw value + validation ====
            //
            $this->validator->validateParameter($param, $rawValue);
            $resolved[] = $rawValue;
        }

        return $resolved;
    }


    // ======================================================
    // Result Normalization
    // ======================================================

    private function normalizeResult(mixed $result): Response
    {
        //
        // TemplateResponse -> convert using RendererInterface
        //
        if ($result instanceof TemplateResponse) {
            /** @var RendererInterface $renderer */
            $renderer = $this->container->get(RendererInterface::class);
            return $result->toResponse($renderer);
        }

        //
        // Raw Response -> use as-is
        //
        if ($result instanceof Response) {
            return $result;
        }

        //
        // Invalid return type
        //
        throw new ControllerInvocationException(
            "Controller must return Response or TemplateResponse; " . gettype($result) . " returned. [Controller:Dispatcher]."
        );
    }

    // ======================================================
    // Scalar Casting
    // ======================================================

    private function castValue(mixed $value, ReflectionNamedType $type, string $name): mixed
    {
        $target = $type->getName();

        return match ($target) {
            "int"    => (int) $value,
            "float"  => (float) $value,
            "bool"   => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            "string" => (string) $value,
            default  => throw new ControllerInvocationException(
                "Unsupported builtin type '{$target}' for parameter '{$name}'. [Controller:Dispatcher]."
            )
        };
    }

    public function dispatchFallback(string $controllerClass, Request $request): Response
    {
        try {
            $controller = $this->container->get($controllerClass);
        } catch (Throwable $e) {
            throw new ControllerInvocationException(
                "Fallback controller '{$controllerClass}' could not be resolved. [Controller:Dispatcher].", previous: $e
            );
        }

        if (!is_callable($controller)) {
            throw new ControllerInvocationException(
                "Fallback controller '{$controllerClass}' is not invokable. [Controller:Dispatcher]."
            );
        }

        return $controller($request);
    }
}

