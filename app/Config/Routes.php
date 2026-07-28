<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/kbbi', 'ApiKBBI::index');
$routes->get('/kbbi/search/(:any)', 'ApiKBBI::search/$1');
