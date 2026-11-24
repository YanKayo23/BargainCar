<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'HomeController::index');


$routes->get('cidades', 'CidadesController::index');

$routes->get('buscar-cidade', static function () {
    return view('cidades_busca');
});

// Pesquisa de anúncios por veículo e cidade
$routes->get('buscar', 'BuscaController::index');

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


// Perfil pessoal do usuário autenticado
$routes->get('meu-perfil', 'PerfilController::index', [
    'filter' => 'auth'
]);


$routes->post(
    'anuncios/(:num)/excluir',
    'AnunciosController::excluir/$1',
    ['filter' => 'auth']
);


$routes->get(
    'anuncios/(:num)/editar',
    'AnunciosController::editar/$1',
    ['filter' => 'auth']
);


$routes->post(
    'anuncios/(:num)/atualizar',
    'AnunciosController::atualizar/$1',
    ['filter' => 'auth']
);

$routes->get(
    'anuncios/(:num)/midias-edicao/(:num)',
    'AnunciosController::exibirMidiaEdicao/$1/$2',
    ['filter' => 'auth']
);

// Perfil privado e gerenciamento da loja ativa
$routes->get('perfil-loja', 'PerfilLojaController::index', ['filter' => 'auth']);
$routes->get('perfil-loja/editar', 'PerfilLojaController::editar', ['filter' => 'auth']);
$routes->post('perfil-loja/atualizar', 'PerfilLojaController::atualizar', ['filter' => 'auth']);
$routes->post('perfil-loja/imagem/(:segment)', 'PerfilLojaController::imagem/$1', ['filter' => 'auth']);

// Voltar ao modo pessoa sem desativar a loja
$routes->post('perfil-loja/sair', 'PerfilLojaController::sair', ['filter' => 'auth']);

// Edição dos dados pessoais do usuário
$routes->get(
    'editar-perfil',
    'PerfilController::editar',
    ['filter' => 'auth']
);

$routes->post(
    'editar-perfil',
    'PerfilController::atualizar',
    ['filter' => 'auth']
);
