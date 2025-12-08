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
		private readonly RequestHandlerInterface    $requestHandler,
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
			$response = $this->requestHandler->handle($request);
		} catch (Throwable $e) {
            // Log + process error via ErrorProcessor
            ErrorProcessor::process(
                $e,
                StatusCode::HTTP_KERNEL_GENERAL_FAILURE,
                'An unexpected error occurred while processing the request.',
            );

            // convert into Response
            $response = $this->createExceptionResponse($e);
		}

        //
        // event fired BEFORE the response is finalized
        //
		$this->eventDispatcher->dispatch(
            new ResponseEvent($request, $response)
        );

		return $response;
	}

    private function createExceptionResponse(Throwable $exception): Response
    {
        //
        // In debug mode, bubble up for a detailed error page / Whoops
        //
        if ($this->debug) {
            throw $exception;
        }

        if ($exception instanceof HttpException) {
            return new Response(
                $exception->getMessage(),
                $exception->getStatusCode(),
            );
        }

        return new Response(
            'Server error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

	public function terminate(Request $request, Response $response): void
	{
        $end = microtime(true);
        $duration = $end - $request->getStartTime();

        //
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
        // Kernel-level cleanup (still allowed)
        //
		$request->getSession()->clearFlash();
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



