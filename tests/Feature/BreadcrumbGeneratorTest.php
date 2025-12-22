<?php

use JDS\Http\Navigation\BreadcrumbGenerator;

it('generates breadcrumbs for a given route', function () {
   $routePrefix = '/pec';

   // sample routes with metadata
    $routes = [
        'metadata' => [
            [
                'uri' => '/',
               'label' => 'Home',
                'path' => null,
                'role' => 15,
                'permission' => 1
            ],
            [
                'uri' => '/about',
                'label' => 'About',
                'path' => '/',
                'role' => 15,
                'permission' => 1
            ],
        ],
    ];

    // simulate $_SERVER['REQUEST_URI'] to emulate the current active route
    $_SERVER['REQUEST_URI'] = '/pec/about';

    // instantiate BreadcrumbGenerator with provided routes and routePrefix
    $breadcrumbGenerator = new BreadcrumbGenerator($routes, $routePrefix);

    // call the generateBreadcrumbs method
    $breadcrumbs = $breadcrumbGenerator->generateBreadcrumbs();

    // assert that the breadcrumbs are generated as expected
    expect($breadcrumbs)->toBe([
        [
            'label' => 'Home',
            'path' => '/pec/',
        ],
       [
           'label' => 'About',
           'path' => '/pec/about',
       ],
    ]);









});




