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

namespace JDS\Http\Controller;

use JDS\Contracts\Http\Controller\ControllerDispatchResultInterface;
use JDS\Controller\AbstractController;
use JDS\Exceptions\Controller\ControllerMethodNotFoundException;
use JDS\Exceptions\Controller\ControllerNotFoundException;
use JDS\Http\Request;
use JDS\Routing\Route;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ControllerResolver implements ControllerDispatchResultInterface
{

    public function dispatch(Request $request, ContainerInterface $container): array
    {
        $route = $request->getRoute();

        if (!$route instanceof Route) {
            throw new RuntimeException(
                "No Route attatched to Request. Ensure ExtractRouteInfo runs before Controller-Reslover::dispatch."
            );
        }

        [$controllerClass, $method] = $route->getHandler();

        if (!$container->has($controllerClass)) {
            throw new ControllerNotFoundException(
                "Controller '{$controllerClass}' not found in container."
            );
        }

        $controller = $container->get($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new ControllerMethodNotFoundException(
                "Method '{$method}' does not exist on controller '{$controllerClass}'."
            );
        }

        //
        // Legacy support
        //
        if ($controller instanceof AbstractController) {
            $controller->setRequest($request);
        }

        //
        // Legacy return shape:
        //
        return [
            [$controller, $method],
            $request->getRouteParams(),
        ];
    }
}

