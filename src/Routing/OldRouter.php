<?php

namespace JDS\Routing;

use JDS\Contracts\Routing\RouterInterface;
use JDS\Controller\OldAbstractController;
use JDS\Http\Request;
use Psr\Container\ContainerInterface;

class OldRouter implements RouterInterface
{

	public function dispatch(Request $request, ContainerInterface $container): array
	{
		$routeHandler = $request->getRouteHandler();
		$routeHandlerArgs = $request->getRouteHandlerArgs();

		if (is_array($routeHandler)) {

            //
            // Extract only controller + method, ignore middleware & metadata
            //
            [$controllerId, $method] = array_slice($routeHandler, 0, 2);

            $controller = $container->get($controllerId);

            //
            // inject the Request automatically into all AbstractController descendants
            //
            if ($controller instanceof OldAbstractController) {
                $controller->setRequest($request);
            }

            $routeHandler = [$controller, $method];
        }

        return [$routeHandler, $routeHandlerArgs];
	}
}

//			if (is_subclass_of($controller, AbstractController::class)) {
//				$controller->setRequest($request);
//			}
//			$routeHandler = [$controller, $method];
//		}
//
//		return [$routeHandler, $routeHandlerArgs];
