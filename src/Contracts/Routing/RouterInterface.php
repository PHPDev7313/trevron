<?php

namespace JDS\Contracts\Routing;

use JDS\Http\Request;
use Psr\Container\ContainerInterface;

interface RouterInterface
{
	public function dispatch(Request $request, ContainerInterface $container);
}

