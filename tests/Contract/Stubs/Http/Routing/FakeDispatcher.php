<?php

namespace Tests\Contract\Stubs\Http\Routing;

use FastRoute\Dispatcher;

/**
 * Fake FastRoute Dispatcher that returns a predetermined routeInfo payload
 */
final class FakeDispatcher implements Dispatcher
{
    /**
     * @param array $result FastRoute-style payload: [FOUND|NOT_FOUND|MEHTOD_NOT_ALLOWED, handler, vars]
     */
    public function __construct(private array $result)
    {
    }

    /**
     * @inheritDoc
     */
    public function dispatch($httpMethod, $uri): array
    {
        return $this->result;
    }

    public function getData(): array
    {
        return [];
    }
}

