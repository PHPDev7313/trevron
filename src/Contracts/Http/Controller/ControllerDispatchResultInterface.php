<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

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

