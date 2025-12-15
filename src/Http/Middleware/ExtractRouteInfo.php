<?php

namespace JDS\Http\Middleware;


use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Exceptions\Http\HttpException;
use JDS\Exceptions\Http\HttpRequestMethodException;
use JDS\Http\Request;
use JDS\Http\Response;
use function FastRoute\simpleDispatcher;

final class ExtractRouteInfo implements MiddlewareInterface
{
    public function __construct(
        private readonly array  $routes,
    )
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        try {
            //
            // Build FastRoute dispatcher
            //
            $dispatcher = simpleDispatcher(function (RouteCollector $collector) {
                foreach ($this->routes['routes'] as $route) {

                    //
                    // Normalize path
                    //
                    $fullPath = $this->normalizeRoute($route->getPath());

                    //
                    // Register with FastRoute
                    //
                    $collector->addRoute(
                        $route->getMethod(),
                        $fullPath,
                        $route // store Route object as handler
                    );
                }
            });

            $routeInfo = $dispatcher->dispatch(
                $request->getMethod(),
                $request->getPathInfo()
            );
        } catch (Exception $e) {
            throw new StatusException(
                StatusCode::HTTP_ROUTE_DISPATCH_FAILURE,
                "Failed to dispatch route.",
                $e
            );
        }

        return match ($routeInfo[0]) {

            Dispatcher::FOUND => $this->handleFoundRoute($routeInfo, $request, $next),

            Dispatcher::METHOD_NOT_ALLOWED =>
                throw new HttpRequestMethodException(
                    "Allowed method: " . implode(', ', $routeInfo[1]),
                    statusCode: 405
                ),

            default =>
                throw new HttpException("Not Found", 404),
        };
    }

    private function handleFoundRoute(array $routeInfo, Request $request, RequestHandlerInterface $next): Response
    {

        $route = $routeInfo[1];
        $vars = $routeInfo[2]; // dynamic parameters

        //
        // Attatch route to Request
        //
        $request->setRoute($route);
        $request->setRouteParams($vars);


        return $next->handle($request);
    }

    private function normalizeRoute(string $route): string
    {
        $route = trim($route);

        if ($route === '' || $route === '/') {
            return '/';
        }

        return '/' . trim($route, '/');
    }
}


//        [$handlerCallable, $vars] = [$routeInfo[1], $routeInfo[2]];
//
//        $request->setRouteHandler($handlerCallable);
//        $request->setRouteHandlerArgs($vars);
//
//        //
//        // NEW: Extract route-specific middleware from route definition
//        //
//        $routeMiddleware = [];
//
//        if (is_array($handlerCallable) && isset($handlerCallable[2]) && is_array($handlerCallable[2])) {
//            $routeMiddleware = $handlerCallable[2]; // Example: [Authenticate::class]
//        }
//
//        //
//        // Attach middleware TO THE REQUEST, not the old RequestHandler!
//        //
//        $request = $request->withAttribute('route.middleware', $routeMiddleware);
//
//        //
//        // Add route metadata (index 3 of handler definition)
//        //
//        if (isset($handlerCallable[3]) && is_array($handlerCallable[3])) {
//            $request = $request->withAttribute('route.meta', $handlerCallable[3]);
//        }



//
//    private array $routeMeta = []; // stores metadata for the current route
//    public function __construct(private readonly array $routes, private readonly string $routePath, private readonly string $appPath, private readonly string $baseUrl)
//    {
//        $this->setRouteMeta($this->routes['metadata']);
//    }
//
//    /**
//     * @throws HttpException
//     * @throws HttpRequestMethodException
//     * @throws Exception
//     */
//    public function process(Request $request, RequestHandlerInterface $next): Response
//    {
//        // Capture route data during setup
//        $routeData = [];
//        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use (&$routeData) {
//            foreach ($this->routes['routes'] as $route) {
//                // Use unified method for normalization and merging
//                $route[1] = $this->mergeAndNormalizeRoutePath($this->routePath, $route[1]);
//
//                // Add route to dispatcher
//                $routeCollector->addRoute(...$route);
//            }
//
//            // Capture route data from the RouteCollector
//            $routeData = $routeCollector->getData();
//        });
//
//        // Generate the sitemap if it hasn't been updated in a month
//        if ($this->shouldRegenerateSitemap()) {
//            $this->generateSitemap($routeData, $this->baseUrl); // Provide the route data to the sitemap generator
//        }
//
//        // Dispatch a URI to obtain the route info
//        $routeInfo = $dispatcher->dispatch(
//            $request->getMethod(),
//            $request->getPathInfo()
//        );
////        dump($request);
//        switch ($routeInfo[0]) {
//            case Dispatcher::FOUND:
//                // Set $request->routeHandler
//                $request->setRouteHandler($routeInfo[1]);
////                dump($request);
//                // Set $request->routeHandlerArgs
//                $request->setRouteHandlerArgs($routeInfo[2]);
////                dump($request);
//                // Inject route middleware on handler
//                if (is_array($routeInfo[1]) && isset($routeInfo[1][2]) && is_array($routeInfo[1][2]) && count($routeInfo[1][2]) > 0) {
//                    $next->injectMiddleware($routeInfo[1][2]);
//                }
//                break;
//
//            case Dispatcher::METHOD_NOT_ALLOWED:
//                $allowedMethods = implode(', ', $routeInfo[1]);
//                $e = new HttpRequestMethodException("The allowed methods are $allowedMethods");
//                $e->setStatusCode(405);
//                throw $e;
//
//            default:
//                $e = new HttpException("Not found");
//                $e->setStatusCode(404);
//                throw $e;
//        }
////        dd($next->handle($request));
//        return $next->handle($request);
//    }
//
//    /**
//     * Retrieves the metadata information associated with a specific route.
//     *
//     * @return array An array containing the metadata for the route.
//     */
//    public function getRouteMeta(): array
//    {
//        return $this->routeMeta;
//    }
//
//    private function setRouteMeta(?array $routeMeta): void
//    {
//        $this->routeMeta = $routeMeta ?? [];
//    }
//
//
//    private function mergeAndNormalizeRoutePath(string $routePath, string $route): string
//    {
//        // Normalize the routePath
//        $routePath = trim($routePath, '/') !== '' ? '/' . trim($routePath, '/') : '';
//
//        // Normalize and concatenate with the given route
//        $normalizeRoute = rtrim($routePath . '/' . ltrim(trim($route, '/'), '/'), '/');
//        return ($route === '/' ? $normalizeRoute . '/' : $normalizeRoute);
//    }
//
//
//    private function shouldRegenerateSitemap(): bool
//    {
//        $sitemapPath = $this->appPath . '/sitemap.xml'; // Path to your sitemap file
//        $oneMonthInSeconds = 30 * 24 * 60 * 60; // 30 days in seconds
//
//        // Check if the sitemap file does not exist or if it hasn't been updated in the last month
//        return !file_exists($sitemapPath) || (time() - filemtime($sitemapPath) > $oneMonthInSeconds);
//    }
//
//    private function generateSitemap(array $routeData, ?string $baseUrl = null): void
//    {
//        // if baseUrl is null use the instance $this->baseUrl
//        $baseUrl = $baseUrl ?? $this->baseUrl;
//
//        // Create the root XML structure for a sitemap
/*        $sitemap = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');*/
//
//        $lastModified = date('Y-m-d');
//
//        // Loop through each HTTP method (e.g., "GET", "POST")
//        foreach ($routeData[0] as $httpMethod => $routes) {
//            // Loop through each individual route under the given HTTP method
//            foreach ($routes as $path => $handler) {
//                // Skip routes with dynamic placeholders like "/freelance/blogpost/{id}"
//                if (strpos($path, '{') !== false) {
//                    continue;
//                }
//
//                // Add a URL entry to the sitemap
//                $entry = $sitemap->addChild('url');
//                $entry->addChild('loc', htmlspecialchars($baseUrl . $path)); // Encode the URL
//                $entry->addChild('lastmod', $lastModified);                 // Optional: Last modified date
//                $entry->addChild('changefreq', 'monthly');                  // Optional: Change frequency
//                $entry->addChild('priority', '0.8');                        // Optional: Priority
//            }
//        }
//
//        // Save the XML (or you could return the XML string)
//        $sitemapPath = rtrim($this->appPath, '/') . '/sitemap.xml';
//        if (!is_dir(dirname($sitemapPath)) || !is_writable(dirname($sitemapPath))) {
//            throw new \RuntimeException('Sitemap directory is not writable: ' . dirname($sitemapPath));
//        }
//        $sitemap->asXML($sitemapPath);
//    }
