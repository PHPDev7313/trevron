<?php

namespace JDS\Http;


use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\ErrorProcessor;
use JDS\Error\StatusCode;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use Throwable;

final class Kernel
{
	public function __construct(
		private readonly bool                       $debug, // true in dev, false in prod
        private readonly MiddlewareResolverInterface $resolver,
		private readonly ControllerDispatcher $controllerDispatcher,
		private readonly EventDispatcher            $eventDispatcher
	)
	{
	}

    /**
     * Handle the request through the middleware pipeline + controller dispatcher.
     */
	public function handle(Request $request): Response
	{
        //
        // mark request start time for profiling & lifecycle monitoring
        //
        $request->setStartTime(microtime(true));


		try {
            //
            // 1. Resolve middleware stack from the middleware resover
            //
            $middlewareList = $this->resolver->getMiddlewareForRequest($request);

            //
            // 2. Build the pipeline: Middleware -> RouteDispatcher
            //
            $pipeline = new MiddlewareQueue(
                $middlewareList,
                new RouteDispatcher(
                    $this->controllerDispatcher
                )
            );

            //
            // 3. Execute the full pipeline
            //
            $response = $pipeline->handle($request);

        } catch (StatusException $e) {
            //
            // Known framework exception -> convert to safe response
            //
            return $this->createExceptionResponse($e);

		} catch (Throwable $e) {

            //
            // Pass exception to the centralized ErrorProcessor
            // Log + process error via ErrorProcessor
            //
            $wrapped = new StatusException(
                StatusCode::HTTP_KERNEL_GENERAL_FAILURE,
                "Unhandled kernel exception: {$e->getMessage()}",
                $e
            );

            //
            // Build an appropriate Response object
            // convert into Response
            //
            return $this->createExceptionResponse($wrapped);
		}

        //
        // 4. Fire early response event-used for response transformation or logging
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
    private function createExceptionResponse(StatusException $exception): Response
    {
        //
        // In debug mode, bubble up for a detailed error page / Whoops
        //
        if ($this->debug) {
            throw $exception;
        }

        //
        // Fallback error response (safe for production)
        //
        return new Response(
            $exception->getMessage(),
            $exception->getCode()
        );
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




