<?php

namespace JDS\Http\Generators;

class MenuGenerator
{
 
	public function __construct(private string $path, private string $file)
	{
	}

    public function generateMenu(): array
    {
        $filename = ($this->path) . ($this->file);
        $jsonMenu = json_decode(file_get_contents($filename), true);
        return $jsonMenu;
        $menu = [];
        foreach ($this->routes as $route) {
            if ($route[0] === 'GET') {
                $menu[] = [
                    'route' => $this->mergeAndNormalizeRoutePath($this->routePrefix, $route[1]),
                    'lastArray' => $route[2][3]
                ];
            }
        }
        $onlyRoutes = [];
        for ($x=0; $x < count($menu); $x++) {
            $onlyRoutes[] = $menu[$x];
        }

        return $onlyRoutes;
    }

     private function mergeAndNormalizeRoutePath(string $routePath, string $route): string
    {
        // Normalize the routePath
        $routePath = trim($routePath, '/') !== '' ? '/' . trim($routePath, '/') : '';

        // Normalize and concatenate with the given route
        $normalizeRoute = rtrim($routePath . '/' . ltrim(trim($route, '/'), '/'), '/');
        return ($route === '/' ? $normalizeRoute . '/' : $normalizeRoute);
    }


}

