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

// Criação de loja
$routes->get('criar-loja', 'LojasController::criar', [
    'filter' => 'auth'
]);
$routes->post('criar-loja', 'LojasController::salvar', [
    'filter' => 'auth'
]);

// Desativar e reativar loja
$routes->post('lojas/(:num)/desativar', 'LojasController::desativar/$1', [
    'filter' => 'auth'
]);
$routes->post('lojas/(:num)/reativar', 'LojasController::reativar/$1', [
    'filter' => 'auth'
]);


$routes->get('teste-loja', static function () {
    return view('lojas/TesteLoja');
}, [
    'filter' => 'auth'
]);

// Exibir o formulário de criação de anúncios
$routes->get('criar-anuncio', 'AnunciosController::criar', ['filter' => 'auth']);
// Receber os dados e as mídias enviados pelo formulário
$routes->post('anuncios/salvar', 'AnunciosController::salvar', ['filter' => 'auth']);

// caminho anuncio id
$routes->get(
    'anuncios/(:num)',
    'AnunciosController::ver/$1'
);

$routes->get(
    'anuncios/(:num)/midias/(:num)',
    'AnunciosController::exibirMidia/$1/$2'
);

