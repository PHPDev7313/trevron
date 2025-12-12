<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Http\Request;
use JDS\Routing\Route;

class FakeRequest extends Request
{
    public function __construct(string $method, string $uri, string $pathInfo)
    {
        parent::__construct($method, $uri, $pathInfo);
    }

    public function withRouteHandler(Route $route): self
    {
        $this->setRoute($route);
        return $this;
    }

    public function withRouteParams(array $params): self
    {
        $this->setRouteParams($params);
        return $this;
    }
}

