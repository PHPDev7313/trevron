<?php

namespace JDS\Http\Middleware;

use JDS\Handlers\ExceptionFormatter;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;
use Throwable;

class StartSession implements MiddlewareInterface
{
	public function __construct(
		private SessionInterface $session,
		private string $apiPrefix = '/api/'
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		if (!str_starts_with($request->getPathInfo() ?? '', $this->apiPrefix)) {
            try {
                // calls the start method in the Session
                $this->session->start();

                $request->setSession($this->session);
            } catch (Throwable $e) {
                // get_class Exception class name
                // getMessage Exception message
                // getFile File where the exception was thrown
                // getLine Line number where the exception was thrown
                // ExceptionFormatter::formatTrace Nicely formated trace
                error_log(sprintf("[%s] %s in %s on line %d\nTrace: %s",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    ExceptionFormatter::formatTrace($e)
                ));
                throw new SessionStartException(sprintf("Failed to start session or bind it to the request. Reason: %s.\nTrace: %s", $e->getMessage(), ExceptionFormatter::formatTrace($e)), 0, $e);
            }
		}

		return $requestHandler->handle($request);
	}
}

