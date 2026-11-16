<?php

use App\Libraries\NavbarService;

$navbarService = new NavbarService();
$nav = $navbarService->obterDados();

$logado = $nav['logado'];
$usuario = $nav['usuario'];
$lojaAtiva = $nav['lojaAtiva'];
$lojasDesativadas = $nav['lojasDesativadas'];

$nomeUsuario = $usuario['nome'] ?? 'Visitante';
$temLojaAtiva = ! empty($lojaAtiva);
?>

<link rel="stylesheet" href="<?= base_url('css/Navbar.css') ?>">

<header class="bc-navbar">
    <div class="bc-navbar-conteudo">

        <!-- Marca BargainCar -->
        <a href="<?= site_url('/') ?>" class="bc-navbar-marca">
            Bargain<span>Car</span>
        </a>

        <!-- Pesquisa de anúncios -->
        <form
            action="<?= site_url('buscar') ?>"
            method="get"
            class="bc-navbar-pesquisa"
        >
            <div class="bc-navbar-campo bc-navbar-termo">
                <span aria-hidden="true">⌕</span>

                <input
                    type="search"
                    name="termo"
                    placeholder="O que você procura?"
                    aria-label="Pesquisar anúncio"
                    value="<?= esc((string) ($_GET['termo'] ?? '')) ?>"
                >
            </div>

            <div class="bc-navbar-campo bc-navbar-cidade">
                <span aria-hidden="true">⌖</span>

                <input
                    type="text"
                    id="bc-cidade-busca"
                    placeholder="Cidade"
                    aria-label="Selecionar cidade"
                    autocomplete="off"
                >

                <input
                    type="hidden"
                    name="cidade_id"
                    id="bc-cidade-id"
                    value=""
                >

                <div
                    id="bc-cidades-sugestoes"
                    class="bc-navbar-sugestoes"
                    hidden
                ></div>
            </div>

            <button type="submit" class="bc-navbar-buscar">
                Buscar
            </button>
        </form>

        <!-- Favoritos -->
        <a
            href="<?= site_url('favoritos') ?>"
            class="bc-navbar-favoritos"
        >
            <span aria-hidden="true">♡</span>
            Favoritos
        </a>

        <!-- Menu Pessoa / Loja -->
        <div class="bc-navbar-perfis">

            <button
                type="button"
                id="bc-menu-botao"
                class="bc-navbar-perfil-botao"
                aria-expanded="false"
                aria-controls="bc-menu-perfis"
            >
                Pessoa
                <span aria-hidden="true">⌄</span>
            </button>

            <div
                id="bc-menu-perfis"
                class="bc-navbar-menu"
                hidden
            >
                <h3>Escolha como navegar</h3>

                <?php if ($logado): ?>

                    <a
                        href="<?= site_url('meu-perfil') ?>"
                        class="bc-navbar-opcao bc-navbar-atual"
                    >
                        <span>
                            <strong>Pessoa</strong>
                            <small><?= esc($nomeUsuario) ?></small>
                        </span>

                        <strong>Atual</strong>
                    </a>

                    <?php if ($temLojaAtiva): ?>

                        <div class="bc-navbar-opcao">
                            <span>
                                <small>Loja</small>
                                <strong>
                                    <?= esc($lojaAtiva['nome_empresa']) ?>
                                </strong>
                            </span>

                            <span class="bc-navbar-acao">
                                Entrar
                            </span>
                        </div>

                    <?php elseif (empty($lojasDesativadas)): ?>

                        <p class="bc-navbar-aviso">
                            Você ainda não tem uma loja.
                            Crie uma para anunciar como empresa.
                        </p>

                        <a
                            href="<?= site_url('criar-loja') ?>"
                            class="bc-navbar-botao-azul"
                        >
                            Criar minha loja
                        </a>

                    <?php endif; ?>

                    <?php if (! empty($lojasDesativadas)): ?>

                        <?php if (! $temLojaAtiva): ?>
                            <p class="bc-navbar-aviso">
                                Nenhuma loja ativa
                            </p>
                        <?php endif; ?>

                        <details class="bc-navbar-desativadas">
                            <summary>
                                Lojas desativadas
                                (<?= count($lojasDesativadas) ?>)
                            </summary>

                            <?php foreach ($lojasDesativadas as $loja): ?>
                                <div class="bc-navbar-loja-desativada">
                                    <strong>
                                        <?= esc($loja['nome_empresa']) ?>
                                    </strong>

                                    <?php if (! $temLojaAtiva): ?>
                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'lojas/' . $loja['id'] . '/reativar'
                                            ) ?>"
                                        >
                                            <?= csrf_field() ?>

                                            <button type="submit">
                                                Reativar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </details>

                        <?php if ($temLojaAtiva): ?>
                            <p class="bc-navbar-aviso">
                                Reativação indisponível com loja ativa.
                            </p>
                        <?php else: ?>
                            <a
                                href="<?= site_url('criar-loja') ?>"
                                class="bc-navbar-botao-azul"
                            >
                                Nova loja
                            </a>
                        <?php endif; ?>

                    <?php endif; ?>

                <?php else: ?>

                    <p class="bc-navbar-aviso">
                        Entre na sua conta para acessar seus perfis.
                    </p>

                    <a
                        href="<?= site_url('login') ?>"
                        class="bc-navbar-botao-azul"
                    >
                        Entrar
                    </a>

                <?php endif; ?>

            </div>
        </div>

        <!-- Criar anúncio -->
        <a
            href="<?= site_url('criar-anuncio') ?>"
            class="bc-navbar-anunciar"
        >
            <span aria-hidden="true">+</span>
            Anunciar
        </a>

    </div>
</header>

<script src="<?= base_url('js/Navbar.js') ?>" defer></script>
