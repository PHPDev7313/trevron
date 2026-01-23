<?php
/** @noinspection PhpClassCanBeReadonlyInspection */
declare(strict_types=1);
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * FINAL: January 17, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: KernelFINALv12ARCHITECTURE.md
 */
namespace JDS\Http;

use JDS\Contracts\Error\Rendering\ErrorRendererInterface;
use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use JDS\Http\Middleware\MiddlewareQueue;
use Throwable;

final class Kernel
{
	public function __construct(
        private readonly MiddlewareResolverInterface    $resolver,
		private readonly ControllerDispatcherInterface  $controllerDispatcher,
        private readonly EventDispatcher                $eventDispatcher,
        private readonly ErrorRendererInterface         $errorRenderer,
	)
	{
	}

    /**
     * Handle the request through the middleware pipeline + controller dispatcher.
     *
     * MAY THROW - by design (outside error-handling domain)
     */
	public function handle(Request $request): Response
	{
        //
        // mark request start time for profiling & lifecycle monitoring
        //
        $start = microtime(true);
        if ($request->getStartTime() === 0.0) {
            $request->setStartTime($start);
        }

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

            //
            // 4. Fire early response event (may mutate response)
            //
            return $this->dispatchResponseEvent($request, $response);

		} catch (Throwable $e) {

            return $this->errorRenderer->render($request, $e);
		}
    }

    /**
     * Executes after the response has already been sent to the client.
     *
     * This MUST NEVER THROW - by Law
     */
	public function terminate(Request $request, Response $response): void
    {
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

    private function dispatchResponseEvent(Request $request, Response $response): Response
    {
        $event = new ResponseEvent($request, $response);

        $this->eventDispatcher->dispatch($event);

        $final = $event->getResponse();

       if (!$final instanceof Response) {
           return $response;
       }

       return $final;
    }
}

