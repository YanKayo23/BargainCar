<?php
use App\Libraries\NavbarService;

$nav = (new NavbarService())->obterDados();
$logado = $nav['logado'];
$usuario = $nav['usuario'];
$lojaAtiva = $nav['lojaAtiva'];
$lojasDesativadas = $nav['lojasDesativadas'];
$nomeUsuario = $usuario['nome'] ?? 'Visitante';
$temLojaAtiva = !empty($lojaAtiva);
$naLoja = ($nav['modoPerfil'] ?? 'pessoa') === 'loja';
$destinoPerfil = $naLoja ? 'perfil-loja' : 'meu-perfil';

$distancia = (int) ($_GET['distancia'] ?? 100);
if (!in_array($distancia, [10, 25, 50, 75, 100], true)) {
    $distancia = 100;
}
$termo = (string) ($_GET['termo'] ?? '');
$cidadeId = (string) ($_GET['cidade_id'] ?? '');
$cidadeTexto = '';
if ($cidadeId !== '' && ctype_digit($cidadeId)) {
    $cidadeSelecionada = (new \App\Models\CidadeModel())->find($cidadeId);
    if ($cidadeSelecionada) {
        $cidadeTexto = $cidadeSelecionada['nome'] . ' - ' . $cidadeSelecionada['uf'];
    } else {
        $cidadeId = '';
    }
} else {
    $cidadeId = '';
}
?>
<!-- Bootstrap 5: inclua uma única vez no layout geral caso já o utilize. -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('css/Navbar.css') ?>">

<nav id="bc-navbar" class="navbar navbar-expand-xl fixed-top bc-navbar" aria-label="Navegação principal">
  <div class="container-fluid bc-navbar-conteudo">
    <a class="navbar-brand bc-navbar-marca" href="<?= site_url('/') ?>">Bargain<span>Car</span></a>

    <button class="navbar-toggler bc-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bc-navbar-collapse" aria-controls="bc-navbar-collapse" aria-expanded="false" aria-label="Abrir navegação">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse bc-navbar-collapse" id="bc-navbar-collapse">
      <form class="bc-navbar-pesquisa" method="get" action="<?= site_url('buscar') ?>" data-cidades-url="<?= site_url('cidades') ?>" data-logado="<?= $logado ? '1' : '0' ?>" novalidate>
        <div class="bc-navbar-campo bc-navbar-termo">
          <span aria-hidden="true">⌕</span>
          <input type="search" name="termo" placeholder="O que você procura?" aria-label="Pesquisar pelo título" value="<?= esc($termo, 'attr') ?>">
        </div>
        <div class="bc-navbar-campo bc-navbar-cidade">
          <span aria-hidden="true">⌖</span>
          <input type="text" id="bc-cidade-busca" value="<?= esc($cidadeTexto, 'attr') ?>" placeholder="Cidade" aria-label="Selecionar cidade" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="bc-cidades-sugestoes">
          <input type="hidden" name="cidade_id" id="bc-cidade-id" value="<?= esc($cidadeId, 'attr') ?>">
          <div id="bc-cidades-sugestoes" class="bc-navbar-sugestoes" role="listbox" hidden></div>
        </div>
        <!-- Dropdown personalizado: aparência integralmente controlada pelo BargainCar -->
        <div class="bc-navbar-campo bc-navbar-distancia" id="bc-distancia-container">
          <input type="hidden" name="distancia" id="bc-distancia" value="<?= $distancia ?>">
          <button type="button" id="bc-distancia-botao" class="bc-distancia-botao"
                  aria-label="Distância máxima" aria-haspopup="listbox"
                  aria-expanded="false" aria-controls="bc-distancia-opcoes">
            <span id="bc-distancia-texto"><?= $distancia ?> km</span>
            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div id="bc-distancia-opcoes" class="bc-distancia-opcoes" role="listbox" aria-label="Distância máxima" hidden>
            <?php foreach ([10, 25, 50, 75, 100] as $km): ?>
              <button type="button" role="option" class="bc-distancia-opcao<?= $distancia === $km ? ' is-selected' : '' ?>"
                      data-km="<?= $km ?>" aria-selected="<?= $distancia === $km ? 'true' : 'false' ?>"><?= $km ?> km</button>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary bc-navbar-buscar"><span aria-hidden="true">⌕</span> Buscar</button>
      </form>

      <div class="bc-navbar-acoes">
        <?php if ($logado): ?>
          <a class="bc-navbar-meu-perfil" href="<?= site_url($destinoPerfil) ?>" title="Acessar meu perfil">♙ <span>Meu Perfil</span></a>
        <?php else: ?>
          <a class="bc-navbar-meu-perfil" href="<?= site_url('login') ?>" title="Entrar ou criar uma conta">♙ <span>Entrar / Cadastrar</span></a>
        <?php endif; ?>

        <a class="bc-navbar-favoritos" href="<?= $logado ? site_url('favoritos') : site_url('login') ?>"><span aria-hidden="true">♡</span> Favoritos</a>

        <div class="bc-navbar-perfis">
          <button type="button" id="bc-menu-botao" class="bc-navbar-perfil-botao" aria-expanded="false" aria-controls="bc-menu-perfis"><?= $naLoja ? 'Loja' : 'Pessoa' ?> <span aria-hidden="true">⌄</span></button>
          <div id="bc-menu-perfis" class="bc-navbar-menu" hidden>
            <h3>Escolha como navegar</h3>
            <?php if ($logado): ?>
              <?php if ($naLoja): ?>
                <form method="post" action="<?= site_url('perfil-loja/sair') ?>" class="bc-navbar-troca-form">
                  <?= csrf_field() ?>
                  <button type="submit" class="bc-navbar-opcao bc-navbar-troca-botao">
                    <span><strong>Pessoa</strong><small><?= esc($nomeUsuario) ?></small></span>
                    <strong class="bc-navbar-acao">Entrar</strong>
                  </button>
                </form>
              <?php else: ?>
                <a href="<?= site_url('meu-perfil') ?>" class="bc-navbar-opcao bc-navbar-atual">
                  <span><strong>Pessoa</strong><small><?= esc($nomeUsuario) ?></small></span><strong>Atual</strong>
                </a>
              <?php endif; ?>
              <?php if ($temLojaAtiva): ?>
                <a href="<?= site_url('perfil-loja') ?>" class="bc-navbar-opcao<?= $naLoja ? ' bc-navbar-atual' : '' ?>"><span><small>Loja</small><strong><?= esc($lojaAtiva['nome_empresa']) ?></strong></span><span class="bc-navbar-acao"><?= $naLoja ? 'Atual' : 'Entrar' ?></span></a>
              <?php elseif (empty($lojasDesativadas)): ?>
                <p class="bc-navbar-aviso">Você ainda não tem uma loja. Crie uma para anunciar como empresa.</p>
                <a href="<?= site_url('criar-loja') ?>" class="bc-navbar-botao-azul">Criar minha loja</a>
              <?php endif; ?>

              <?php if (!empty($lojasDesativadas)): ?>
                <?php if (!$temLojaAtiva): ?><p class="bc-navbar-aviso">Nenhuma loja ativa</p><?php endif; ?>
                <details class="bc-navbar-desativadas">
                  <summary>Lojas desativadas (<?= count($lojasDesativadas) ?>)</summary>
                  <?php foreach ($lojasDesativadas as $loja): ?>
                    <div class="bc-navbar-loja-desativada">
                      <strong><?= esc($loja['nome_empresa']) ?></strong>
                      <?php if (!$temLojaAtiva): ?>
                        <form method="post" action="<?= site_url('lojas/' . $loja['id'] . '/reativar') ?>">
                          <?= csrf_field() ?>
                          <button type="submit">Reativar</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </details>
                <?php if ($temLojaAtiva): ?>
                  <p class="bc-navbar-aviso">Reativação indisponível com loja ativa.</p>
                <?php else: ?>
                  <a href="<?= site_url('criar-loja') ?>" class="bc-navbar-botao-azul">Nova loja</a>
                <?php endif; ?>
              <?php endif; ?>
            <?php else: ?>
              <p class="bc-navbar-aviso">Entre na sua conta para acessar seus perfis.</p>
              <a href="<?= site_url('login') ?>" class="bc-navbar-botao-azul">Entrar</a>
              <a href="<?= site_url('cadastro') ?>" class="bc-navbar-link-cadastro">Criar uma conta</a>
            <?php endif; ?>
          </div>
        </div>
        <a href="<?= $logado ? site_url('criar-anuncio') : site_url('login') ?>" class="btn btn-primary bc-navbar-anunciar"><span aria-hidden="true">+</span> Anunciar</a>
      </div>
    </div>
  </div>
</nav>
<!-- Reserva a altura real da navbar fixa, inclusive quando ela quebra em linhas. -->
<div id="bc-navbar-espacador" aria-hidden="true"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?= base_url('js/Navbar.js') ?>" defer></script>
