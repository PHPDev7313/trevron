<?php

namespace Tests\Stubs\Http;

use JDS\Http\Request;
use JDS\Routing\Route;

class FakeRequest extends Request
{
    private ?Route $route = null;

    public function __construct(string $method, string $uri, string $pathInfo)
    {
        parent::__construct($method, $uri, $pathInfo);
    }

    public function setRoute(Route $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

}


