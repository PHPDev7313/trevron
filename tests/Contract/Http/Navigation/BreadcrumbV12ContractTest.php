<?php

use JDS\Routing\ProcessRoutes;

it('[v1.2 FINAL] navigation metadata is sufficient to generate breadcrumbs', function () {
    $processed = ProcessRoutes::process([
        [
            'GET',
            '/',
            [
                'HomeController',
                'index',
                [],
                [
                    'label' => 'Home',
                    'path' => null,
                    'requires_token' => false,
                ],
            ],
        ],
        [
            'GET',
            '/roles',
            [
                'RoleController',
                'index',
                [],
                [
                    'label' => 'Roles',
                    'path' => '/',
                    'requires_token' => false,
                ]
            ]
        ]
    ]);

    $breadcrumbs = [];

    foreach ($processed->metadata->all() as $item) {
        $breadcrumbs[$item['uri']] = $item['label'];
    }

    expect($breadcrumbs)->toBe([
        '/' => 'Home',
        '/roles' => 'Roles',
    ]);
});







