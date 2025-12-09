<?php

namespace JDS\Http;


use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\ErrorProcessor;
use JDS\Error\StatusCode;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use Throwable;

final class Kernel
{
	public function __construct(
		private readonly bool                       $debug, // true in dev, false in prod
		private readonly RequestHandlerInterface    $middlewareResolver, // resoves middleware list per request
		private readonly EventDispatcher            $eventDispatcher
	)
	{
	}

	public function handle(Request $request): Response
	{
        //
        // mark request start time for profiling & lifecycle monitoring
        //
        $request->setStartTime(microtime(true));


		try {
            //
            // Resolve middleware stack from the middleware resover
            //
            $middlewareList = $this->resolveMiddlewareList($request);

            //
            // Build the pipeline: Middleware -> RouteDispatcher
            //
            $pipeline = new MiddlewareQueue(
                $middlewareList,
                new RouteDispatcher()
            );

            //
            // Execute the full pipeline
			$response = $pipeline->handle($request);
		} catch (Throwable $e) {
            //
            // Pass exception to the centralized ErrorProcessor
            // Log + process error via ErrorProcessor
            //
            ErrorProcessor::process(
                $e,
                StatusCode::HTTP_KERNEL_GENERAL_FAILURE,
                'An unexpected error occurred while processing the request.',
            );

            //
            // Build an appropriate Response object
            // convert into Response
            //
            $response = $this->createExceptionResponse($e);
		}

        //
        // Fire early response event-used for response transformation or logging
        // event fired BEFORE the response is finalized
        //
		$this->eventDispatcher->dispatch(
            new ResponseEvent($request, $response)
        );

		return $response;
	}

    /**
     * Responsible for creating the Response object when an exception occors.
     */
    private function createExceptionResponse(Throwable $exception): Response
    {
        //
        // In debug mode, bubble up for a detailed error page / Whoops
        //
        if ($this->debug) {
            throw $exception;
        }

        //
        // If this is an HttpException, let it control status and message
        //
        if ($exception instanceof HttpException) {
            return new Response(
                $exception->getMessage(),
                $exception->getStatusCode(),
            );
        }

        //
        // Fallback error response (safe for production)
        //
        return new Response(
            'Server error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    /**
     * Allows the Kernel to resolve middleware dynamically
     * Can be expanded later for route-based middleware.
     */
    private function resolveMiddlewareList(Request $request): array
    {
        //
        // The `$middlewareResolver` MUST implement RequestHandlerInterface,
        // and provide a method like getMiddlewareForRequest() or similar.
        //
        // For now, we assume it's the old RequestHandler repurposed into a resolver.
        //
        if (method_exists($this->middlewareResolver, 'getMiddlewareForRequest')) {
            return $this->middlewareResolver->getMiddlewareForRequest($request);
        }

        //
        // Fallback: treat the resolver itself as the single handler
        //
        return [$this->middlewareResolver];
    }

    /**
     * Executes after the response has already been sent to the client.
     */
	public function terminate(Request $request, Response $response): void
    {
        $end = microtime(true);
        $duration = $end - $request->getStartTime();

        //
        // Lifecycle monitoring, profiling, activity logging
        // MAIN TERMINATION EVENT
        //
        $this->eventDispatcher->dispatch(
            new TerminateEvent(
                $request,
                $response,
                $request->getStartTime(),
                $end,
                $duration
            )
        );

        //
        // Kernel-level cleanup: always safe to do here
        // Kernel-level cleanup (still allowed)
        //
        if ($session = $request->getSession()) {
            $session->clearFlash();
        }
	}
}



//		if ($request->getSession()->isAuthenticated()) {
//			$request->getSession()?->remove(SessionAuthentication::AUTH_KEY);
//		}
//
//
//
//	private function handleException(Request $request, Throwable $e): Response
//	{
//        //
//        // In debug mode, rethrow to show stack traces
//        //
//		if ($this->config->debug) {
//			throw $e;
//		}
//
//		return $this->renderException($e);
//	}
//
//    private function renderException(Throwable $e): Response
//    {
//        if ($e instanceof HttpException) {
//            return new Response(
//                $e->getMessage(),
//                $e->getStatusCode(),
//            );
//        }
//
//        return new Response(
//            'Server error',
//            Response::HTTP_INTERNAL_SERVER_ERROR,
//        );
//    }



