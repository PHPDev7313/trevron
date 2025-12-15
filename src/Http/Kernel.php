<?php

namespace JDS\Http;


use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Controller\Error\NotFoundController;
use JDS\Error\StatusCode;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use JDS\Http\Middleware\MiddlewareQueue;
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
        // MAY THROW - by design
        //

        //
        // mark request start time for profiling & lifecycle monitoring
        //
        $start = microtime(true);
        $request->setStartTime($start);


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
            if ($e->getCode() === StatusCode::HTTP_ROUTE_NOT_FOUND) {
                $controller = $this->controllerDispatcher
                    ->dispatchFallback(NotFoundController::class, $request);

                return $controller;
            }

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
        return $this->dispatchResponseEvent($request, $response);
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
        // This MUST NEVER THROW - by Law
        try {
            $end = microtime(true);
            $duration = max(0.0, ($end - $request->getStartTime()));

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
        } catch (Throwable $e) {
            //
            // ABSOLUTELY NEVER throw from terminate()
            //
            // Optional: log to PHP error log or a faisafe logger
            //
            error_log(
                sprintf(
                    '[Kernel::terminate] Swallowed exception: %s (%s:%d)',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
	}

    private function dispatchResponseEvent(Request $request, Response $reponse): Response
    {
        $event = new ResponseEvent($request, $reponse);

        $this->eventDispatcher->dispatch($event);

        return $event->getResponse();
    }
}

