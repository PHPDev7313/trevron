<?php

namespace Tests\Contract\Stubs\Http\Routing;

use FastRoute\Dispatcher;

/**
 * Fake FastRoute Dispatcher that throws (infrastructure failure)
 */
final class ExplodingDispatcher implements Dispatcher
{

    /**
     * @inheritDoc
     */
    public function dispatch($httpMethod, $uri)
    {
        throw new \RuntimeException('dispatcher failure');
    }

    public function getData(): array
    {
        return [];
    }
}

