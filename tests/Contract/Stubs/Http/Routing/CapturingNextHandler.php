<?php

namespace Tests\Contract\Stubs\Http\Routing;

use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;
use Closure;

/**
 * Minimal RequestHandler for middleware pipeline
 */
final class CapturingNextHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Closure $assert
    )
    {
    }

    public function handle(Request $request): Response
    {
        ($this->assert)($request);
        return new Response('OK', 200);
    }
}

