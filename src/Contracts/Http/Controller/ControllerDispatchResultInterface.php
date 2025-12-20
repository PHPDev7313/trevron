<?php

namespace JDS\Contracts\Http\Controller;

use JDS\Http\Request;
use Psr\Container\ContainerInterface;

interface ControllerDispatchResultInterface
{
    /**
     * @return array{0: callable, 1: array}
     */
	public function dispatch(Request $request, ContainerInterface $container): array;
}

