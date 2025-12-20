<?php

namespace Tests\Contract\Stubs\Http\Routing;

use FastRoute\Dispatcher;

final class FakeDispatcher implements Dispatcher
{

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
}