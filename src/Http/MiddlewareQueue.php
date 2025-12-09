<?php

namespace JDS\Http;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\ErrorProcessor;
use JDS\Error\StatusCode;
use Throwable;

class MiddlewareQueue implements RequestHandlerInterface
{


    private RequestHandlerInterface $finalHandler;

    public function __construct(
        /** @var MiddlewareInterface[] */
        private array $middlewares,
        RequestHandlerInterface $finalHandler
    )
    {
        $this->middlewares = array_values($this->middlewares);
        $this->finalHandler = $finalHandler;
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->handleAtIndex($request, 0);
        } catch (Throwable $e) {
            //
            // Any unhandled exception in the entire pipeline
            //
            ErrorProcessor::process(
                $e,
                StatusCode::HTTP_PIPELINE_FAILURE,
                "Unhandled exception in HTTP middleware pipeline."
            );

            return new Response("Internal Server Error", 500);
        }
    }

    public function handleAtIndex(Request $request, int $index): Response
    {
        if (!isset($this->middlewares[$index])) {
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->middlewares[$index];

        return $middleware->process(
            $request,
            new class($this, $index) implements RequestHandlerInterface {
                public function __construct(
                    private MiddlewareQueue $queue,
                    private int $index
                ) {}

                public function handle(Request $request): Response
                {
                    return $this->queue->handleAtIndex($request, $this->index + 1);
                }
            }
        );
    }
}

