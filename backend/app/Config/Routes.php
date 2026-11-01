<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('cidades', 'CidadesController::index');
$routes->get('buscar-cidade', static function () {
    return view('cidades_busca');
});
