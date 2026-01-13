<?php
/** @noinspection PhpClassCanBeReadonlyInspection */
declare(strict_types=1);

namespace JDS\Http\Middleware;


use JDS\Contracts\Http\ControllerDispatcherInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

final class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ControllerDispatcherInterface $controllerDispatcher
    ) {}

    public function handle(Request $request): Response
    {
        return $this->controllerDispatcher->dispatch($request);
    }
}


