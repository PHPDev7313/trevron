<?php

namespace JDS\Http\Middleware;

use JDS\Authentication\RuntimeException;
use JDS\Http\Request;
use JDS\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RequestHandler implements RequestHandlerInterface
{
	private array $middleware = [
		ExtractRouteInfo::class,
		StartSession::class,
		VerifyCsrfToken::class,
		RouterDispatch::class
	];

    private array $routeMeta = []; // stores route metadata and other attributes

	public function __construct(private ContainerInterface $container)
	{
        $this->setRouteMeta($this->container->get('config')->get('routes')['metadata']);
	}

    private function setRouteMeta(array $routeMeta): void
    {
        $this->routeMeta = $routeMeta;
    }

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function handle(Request $request): Response
	{
		// if there are no middleware classes to execute, return a default response
		// a response should have been returned before the list becomes empty

		if (empty($this->middleware)) {
			return new Response("It's totally borked, mate. Contact support", 500);
		}

		// get the next middleware class to execute
		$middlewareClass = array_shift($this->middleware);

		$middleware = $this->container->get($middlewareClass);

		// create a new instance of the middleware call process on it
		$response = $middleware->process($request, $this);
        // validate the response is a valid Response object
        if (!($response instanceof Response)) {
            // verbiage
            throw new RuntimeException(sprintf('Middleware [%s] did not return a valid Response object in Request Handler.', $middlewareClass));
        }
		return $response;
	}

	public function injectMiddleware(array $middleware): void
	{
		array_splice($this->middleware, 0, 0, $middleware);
	}

	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}

    /**
     * Sets a metadata attribute for the route.
     *
     * @param string $key The metadata attribute key.
     * @param mixed $value The metadata attribute value to be set.
     * @return void
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->routeMeta[$key] = $value;
    }


    /**
     * Retrieves the value of a specified metadata attribute.
     *
     * @param string $key The attribute key to retrieve
     * @return mixed The value of the specified attribute, or null if the key does not exist
     */
    public function getAttribute(string $key): mixed
    {
        return $this->routeMeta[$key] ?? null;
    }

    /**
     * Retrieves all route metadata or shared attributes.
     *
     * @return array The metadata or attributes set during request handling.
     */
    public function getRouteMeta(): array
    {
        return $this->routeMeta;
    }
}


