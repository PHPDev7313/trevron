<?php

namespace JDS\Routing;

use JDS\Contracts\Routing\RouterInterface;
use JDS\Controller\AbstractController;
use JDS\Exceptions\Controller\ControllerMethodNotFoundException;
use JDS\Exceptions\Controller\ControllerNotFoundException;
use JDS\Http\Request;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Router implements RouterInterface
{

    public function dispatch(Request $request, ContainerInterface $container)
    {
        $route = $request->getRoute();

        if (!$route instanceof Route) {
            throw new RuntimeException(
                "No Route attatched to Request. Ensure ExtractRouteInfo runs before Router::dispatch()."
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
            $request->getRouteParam(),
        ];
    }
}