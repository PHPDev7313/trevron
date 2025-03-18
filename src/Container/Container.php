<?php

namespace JDS\Container;

use JDS\Handlers\ExceptionHandler;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;


// This is an example to teach how containers work
// this is NOT used in the framework to build a container
class Container implements ContainerInterface
{
	private array $services = [];

    /**
     * Adds a service to the container.
     *
     * @param string $id The identifier of the service.
     * @param string|object|null $concrete The concrete implementation of the service,
     *                                     or null to use the class identified by $id.
     *
     *
     */
	public function add(string $id, string|object $concrete = null): void
	{
        try {
            if (is_null($concrete)) {
                if (!class_exists($id)) {
                    throw new ContainerException("Service {$id} could not be found!");
                }
                $concrete = $id;
            }
            if (isset($this->services[$id])) {
                throw new ContainerException("Service {$id} is already defined in the container.");
            }
            $this->services[$id] = $concrete;
        } catch (ContainerException $e) {
            ExceptionHandler::render(
                $e,
                "Failed to add service: {$id}",
                $this->isProduction()
            );
            exit(90);
        } catch (Throwable $e) {
            ExceptionHandler::render(
                $e,
                "An Unexpected error occurred while adding service: {$id}.",
                $this->isProduction()
            );
            exit(1);
        }
	}

    /**
     * Retrieves a service or object by its identifier.
     *
     * @param string $id The identifier of the service to retrieve.
     * @return mixed The resolved service or object.
     * @throws ContainerException If the service does not exist and the class cannot be resolved.
     */
	public function get(string $id)
	{
        try {
            // check if the service exists in the container
            if (!$this->has($id)) {
                // if not found, check if the class exists
                if (!class_exists($id)) {
                    throw new ContainerException("Service {$id} could not be resolved.");
                }
                $this->add($id);
            }
            // resolve and return the service object
            return $this->resolve($this->services[$id]);

        } catch (ContainerException $e) {
            ExceptionHandler::render(
                $e,
                "Failed to resolve service: {$id}",
                $this->isProduction()
            );
            exit(90);
        } catch (Throwable $e) {
            ExceptionHandler::render(
                $e,
                "An Unexpected error occurred while resolving service: {$id}.",
                $this->isProduction());
            exit(1);
        }
	}

    /**
     * Resolves and instantiates a class using reflection and dependency injection.
     *
     * @param string|object $class The class name or identifier to be resolved and instantiated.
     * @return object The instantiated class object.
     * @throws \ReflectionException If the class cannot be reflected.
     */
	private function resolve($class) : object
	{
        try {
            // 1 instantiate a reflection class (dump to check)
            $reflectionClass = new \ReflectionClass($class);

            // 2 use reflection to try to obtain a class constructor (if any)
            $constructor = $reflectionClass->getConstructor();

            // 3 if there is no constructor, simply instantiate
            if (is_null($constructor)) {
                return $reflectionClass->newInstance();
            }

            // 4 get the constructor parameters
            $constructorParams = $constructor->getParameters();

            // 5 Resolve the dependencies of the class
            $classDependencies = $this->resolveClassDependencies
            ($constructorParams);

            // 6 instantiate with dependencies and return the object
            return $reflectionClass->newInstanceArgs($classDependencies);

        } catch (ReflectionException $e) {
            // Reflection-specific error handling
            ExceptionHandler::render(
                $e,
                "Failed to reflect on class: {$class}.",
                $this->isProduction()
            );
            exit(1);
        } catch (Throwable $e) {
            ExceptionHandler::render(
                $e,
                "An unexpected error occurred while resolving class: {$class}.",
                $this->isProduction()
            );
            exit(1);
        }
	}

    /**
     * Resolves and instantiates dependencies for a class based on reflection parameters.
     *
     * @param array $reflectionParameters An array of \ReflectionParameter objects representing the constructor parameters of a class.
     * @return array An array of resolved dependencies corresponding to the provided reflection parameters.
     * @throws ContainerException If a required dependency cannot be resolved.
     */
	private function resolveClassDependencies(array $reflectionParameters): array
	{
		// 1 initialize empty dependencies array (required by newInstanceArgs)
		$classDependencies = [];

		// 2 try to locate and instantiate each parameter
		/** @var ReflectionParameter $parameter */
		foreach ($reflectionParameters as $parameter) {
            try {
                // get the parameter's ReflectionNamedType as $serviceType
                $serviceType = $parameter->getType();

                // ensure serviceType is valid and resolvable
                if (!$serviceType instanceof ReflectionNamedType || !$serviceType->isBuiltin()) {
                    throw new ContainerException("Unable to resolve parameter: {$parameter->getName()} - invalid or primitive type detected.");
                }
                // try to instantiate using $serviceType's name
                $service = $this->get($serviceType->getName());

                // add the service to the classDependencies array
                $classDependencies[] = $service;
            } catch (ContainerException $e) {
                ExceptionHandler::render(
                    $e,
                    "Failed to resolve dependency for parameter: {$parameter->getName()}.",
                    $this->isProduction()
                );
            } catch (Throwable $e) {
                ExceptionHandler::render(
                    $e,
                "An unexpected error occurred while resolving parameter: {$parameter->getName()}.",
                $this->isProduction()
                );
            }
		}

		// 3 return the classDependencies array
		return $classDependencies;
	}

    /**
     * Checks if a service or object exists in the container by its identifier.
     *
     * @param string $id The identifier of the service to check for existence.
     * @return bool True if the service exists, false otherwise.
     */
	public function has(string $id): bool
	{
        // ensure the identifier is valid
        if (empty($id)) {
            ExceptionHandler::log("Invalid or empty service identifier passed to 'has' method.");
            // invalid or empty identifier cannot be valid
            return false;
        }

        // check if the service exists
		return array_key_exists($id, $this->services);
	}

    private function isProduction(): bool
    {
        return $_ENV['APP_ENV'] === 'prod';
    }
}

