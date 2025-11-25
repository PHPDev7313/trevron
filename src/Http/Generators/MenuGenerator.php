<?php

namespace JDS\Http\Generators;


use JDS\Contracts\Http\Generators\MenuGeneratorInterface;
use JDS\Http\InvalidArgumentException;

class MenuGenerator implements MenuGeneratorInterface
{
 
	public function __construct(private string $path, private string $file)
	{
	}
    
    public function generateMenu(?string $roleId=null): array
    {
        if ($roleId === null) {
            throw new InvalidArgumentException('There must be a Role ID. User MUST be logged in!');
        }
        $filename = ($this->path) . ($this->file);
        $jsonMenu = json_decode(file_get_contents($filename), true);
        $generatedMenus = [];
        $menus = $jsonMenu['menus'];
        $smenus = $jsonMenu['smenus'];
        $tmenus = $jsonMenu['tmenus'];
        
        // filter menus based on role_id
        $filteredMenus = array_filter($menus, function ($menu) use ($roleId) {
            return isset($menu['role_id']) && $menu['role_id'] === $roleId;
        });
        foreach ($filteredMenus as $menu) {
            // filter through the smenus where menu_id matches
            $filteredSmenus = array_filter($smenus, function ($smenu) use ($menu) {
                return $menu['menu_id'] === $smenu['menu_id'];
            });
            
            // add the filterd smenus to the menu
            $menu['smenu'] = [];
            foreach ($filteredSmenus as $smenu) {
                // filter through tmenus where smenu_id matches
                $filteredTmenus = array_filter($tmenus, function ($tmenu) use ($smenu) {
                    return $smenu['smenu_id'] === $tmenu['smenu_id'];
                });
                // add the filtered tmenus to the smenu
                $smenu['tmenu'] = $filteredTmenus;
                $menu['smenu'][] = $smenu;
            }
            $generatedMenus[] = $menu;
        }
        return $generatedMenus;
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

