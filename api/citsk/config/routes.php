<?php
$routes = [
    [
        'path'       => '/^\/api\/auth\//',
        'controller' => 'Citsk\Controllers\UserController',
    ],

    [
        'path'       => '/^\/api\/identity\//',
        'controller' => 'Citsk\Controllers\UserController',
    ],

    [
        'path'       => '/\/api\/route\//',
        'controller' => 'Citsk\Controllers\MultiRouteController',
    ],
];
