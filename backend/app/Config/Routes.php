<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('cidades', 'CidadesController::index');

$routes->get('buscar-cidade', static function () {
    return view('cidades_busca');
});

$routes->get('cadastro', 'UsuariosController::cadastro');
$routes->post('cadastro', 'UsuariosController::salvar');

// Página de login
$routes->get('login', 'AuthController::login');
// Processamento do login
$routes->post('login', 'AuthController::autenticar');

$routes->post('logout', 'AuthController::sair');
