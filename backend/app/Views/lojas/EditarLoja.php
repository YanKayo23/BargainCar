<?php
/**
 * Dados enviados por PerfilLojaController::index().
 * As anotações permitem que o Intelephense reconheça as variáveis da View.
 * @var array<string, mixed> $loja
 * @var array<string, mixed>|null $cidade
 * @var array<int, array<string, mixed>> $anuncios
 * @var array<string, int> $totais
 */
$loja = isset($loja) && is_array($loja) ? $loja : [];
$cidade = isset($cidade) && is_array($cidade) ? $cidade : null;
$anuncios = isset($anuncios) && is_array($anuncios) ? $anuncios : [];
$totais = array_merge(
    ['ativos' => 0, 'pendentes' => 0, 'expirados' => 0, 'excluidos' => 0],
    isset($totais) && is_array($totais) ? $totais : []
);
$historico = $historico ?? [];
$nome = (string) ($loja['nome_empresa'] ?? 'Loja');
$cidadeTexto = $cidade ? ($cidade['nome'] ?? '') . ' - ' . ($cidade['uf'] ?? '') : 'Cidade não informada';
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));
$cadastro = !empty($loja['data_cadastro']) ? new DateTimeImmutable((string) $loja['data_cadastro']) : null;
$tempo = $cadastro ? $cadastro->diff(new DateTimeImmutable())->days . ' dias' : 'tempo não informado';
$mensagemSucesso = session()->getFlashdata('sucesso');
$mensagemErro = session()->getFlashdata('erro');
$erros = session()->getFlashdata('erros') ?? [];
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($nome) ?> | BargainCar</title>
  <link rel="stylesheet" href="<?= base_url('css/PerfilLoja.css') ?>">
  <script src="<?= base_url('js/PerfilLoja.js') ?>" defer></script>
</head>
<body>
<?= view('layouts/Navbar') ?>
<main class="pl-container">
  <a href="<?= site_url('/') ?>" class="pl-voltar">← Voltar à Home</a>
  <?php if ($mensagemSucesso): ?><div class="pl-aviso pl-ok" role="status"><?= esc($mensagemSucesso) ?></div><?php endif; ?>
  <?php if ($mensagemErro): ?><div class="pl-aviso pl-erro" role="alert"><?= esc($mensagemErro) ?></div><?php endif; ?>
  <?php if ($erros): ?><div class="pl-aviso pl-erro" role="alert"><?= esc(implode(' ', array_values($erros))) ?></div><?php endif; ?>

  <section class="pl-identidade" aria-label="Identidade da loja">
    <div class="pl-capa">
      <?php if (!empty($loja['foto_capa'])): ?><img src="<?= esc(base_url($loja['foto_capa']), 'attr') ?>" alt="Capa da loja"><?php else: ?><span>Foto de capa da empresa</span><?php endif; ?>
      <form method="post" action="<?= site_url('perfil-loja/sair') ?>" class="pl-sair-form">
        <?= csrf_field() ?>
        <button type="submit" class="pl-btn pl-sair">Sair da loja</button>
      </form>
    </div>
    <div class="pl-info">
      <div class="pl-avatar">
        <?php if (!empty($loja['foto_perfil'])): ?><img src="<?= esc(base_url($loja['foto_perfil']), 'attr') ?>" alt="Logotipo da loja"><?php else: ?><span><?= esc($inicial) ?></span><?php endif; ?>
      </div>
      <div class="pl-dados"><h1><?= esc($nome) ?></h1><p><?= esc($cidadeTexto) ?> · Na plataforma há <?= esc($tempo) ?></p>
      </div>
      <span class="pl-btn pl-contorno pl-editar-futuro" title="A edição será criada em uma página separada">Editar loja (em breve)</span>
    </div>
  </section>

  <section class="pl-detalhes" aria-labelledby="pl-detalhes-titulo">
    <div class="pl-secao-topo"><div><h2 id="pl-detalhes-titulo">Informações da minha loja</h2><p>Informações administrativas visíveis somente para o proprietário.</p></div><span class="pl-situacao">Loja ativa</span></div>
    <div class="pl-detalhes-grid">
      <div><span>Nome da empresa</span><strong><?= esc($nome) ?></strong></div>
      <div><span>Cidade</span><strong><?= esc($cidadeTexto) ?></strong></div>
      <div><span>CNPJ</span><strong><?= !empty($loja['cnpj']) ? esc(preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', (string) $loja['cnpj'])) : 'Não informado' ?></strong></div>
      <div><span>Contato</span><strong><?= esc($loja['contato'] ?? 'Não informado') ?></strong></div>
      <div><span>Data de cadastro</span><strong><?= $cadastro ? esc($cadastro->format('d/m/Y')) : 'Não informada' ?></strong></div>
      <div><span>Identificador da loja</span><strong>#<?= (int) $loja['id'] ?></strong></div>
    </div>
    <div class="pl-resumo">
      <div><strong><?= (int) $totais['ativos'] ?></strong><span>Ativos</span></div>
      <div><strong><?= (int) $totais['pendentes'] ?></strong><span>Pendentes</span></div>
      <div><strong><?= (int) $totais['expirados'] ?></strong><span>Expirados</span></div>
      <div><strong><?= (int) $totais['excluidos'] ?></strong><span>Excluídos</span></div>
    </div>
  </section>

  <section class="pl-anuncios">
    <div class="pl-secao-topo"><div><h2>Anúncios da loja</h2><p>Gerencie os anúncios publicados em nome da empresa.</p></div><a class="pl-btn pl-azul" href="<?= site_url('criar-anuncio') ?>">+ Novo anúncio da loja</a></div>
    <?php if (!$anuncios): ?><div class="pl-vazio">Esta loja ainda não possui anúncios.</div><?php else: ?>
    <div class="pl-grid">
      <?php foreach ($anuncios as $anuncio): ?>
      <?php $visivel = empty($anuncio['deleted_at']) && in_array($anuncio['status'], ['ativo', 'pendente'], true) && !empty($anuncio['data_expiracao']) && strtotime($anuncio['data_expiracao']) > time(); ?>
      <article class="pl-anuncio">
        <div class="pl-foto"><?php if (!empty($anuncio['foto_id'])): ?><img loading="lazy" src="<?= site_url('anuncios/' . $anuncio['id'] . '/midias-edicao/' . $anuncio['foto_id']) ?>" alt="Foto de <?= esc($anuncio['titulo'], 'attr') ?>"><?php else: ?><span>Sem foto disponível</span><?php endif; ?></div>
        <div class="pl-card-conteudo"><span class="pl-status"><?= esc($visivel ? ucfirst($anuncio['status']) : 'Indisponível') ?></span><h3><?= esc($anuncio['titulo']) ?></h3><strong>R$ <?= number_format((float)$anuncio['preco'], 2, ',', '.') ?></strong><p><?= esc($anuncio['ano']) ?> · <?= esc($anuncio['cor']) ?></p>
        <?php if ($visivel && $anuncio['status'] === 'ativo'): ?><a class="pl-ver" href="<?= site_url('anuncios/' . $anuncio['id']) ?>">Ver anúncio</a><?php else: ?><span class="pl-ver pl-inativo">Visualização indisponível</span><?php endif; ?>
        <?php if (empty($anuncio['deleted_at'])): ?>
          <div class="pl-card-acoes">
            <a class="pl-btn pl-contorno" href="<?= site_url('anuncios/' . $anuncio['id'] . '/editar?origem=loja') ?>">Editar</a>
            <button type="button" class="pl-btn pl-excluir" data-excluir-anuncio data-url="<?= esc(site_url('anuncios/' . $anuncio['id'] . '/excluir'), 'attr') ?>" data-titulo="<?= esc($anuncio['titulo'], 'attr') ?>">Excluir</button>
          </div>
        <?php endif; ?></div>
      </article>
      <?php endforeach; ?>
    </div><?php endif; ?>
  </section>
  <section class="pl-anuncios pl-historico" aria-labelledby="pl-historico-titulo">
    <div class="pl-secao-topo"><div><h2 id="pl-historico-titulo">Histórico de anúncios</h2><p>Somente você pode visualizar este histórico.</p></div>
      <label>Filtrar: <select id="pl-filtro-historico"><option value="todos">Todos</option><option value="excluido">Excluídos</option><option value="expirado">Expirados</option><option value="moderacao">Removidos por moderação</option></select></label>
    </div>
    <div id="pl-historico-vazio" class="pl-vazio" <?= $historico ? 'hidden' : '' ?>>Nenhum anúncio nesta situação.</div>
    <div class="pl-historico-lista">
<?php foreach ($historico ?? [] as $item): ?>
<?php $situacao = $item['situacao_historico']; $acao = \App\Services\AnuncioCicloVidaService::acao($item); $data = $situacao === 'expirado' ? $item['data_expiracao'] : $item['deleted_at']; ?>
<article class="pl-historico-item" data-situacao="<?= esc($situacao,'attr') ?>">
<div class="pl-historico-foto"><?php if ($item['foto_id']): ?><img loading="lazy" src="<?= site_url('anuncios/'.$item['id'].'/midias-historico/'.$item['foto_id']) ?>" alt="Foto do veículo"><?php else: ?>Sem foto<?php endif; ?></div>
<div class="pl-historico-dados"><h3><?= esc($item['titulo']) ?></h3><strong>R$ <?= number_format((float)$item['preco'],2,',','.') ?></strong>
<p><?= esc(['excluido'=>'Excluído','expirado'=>'Expirado','moderacao'=>'Removido por moderação'][$situacao]) ?> · <?= $data ? esc(date('d/m/Y',strtotime($data))) : 'Data não informada' ?></p>
<?php if ($acao === 'restaurar' && !($somenteConsulta ?? false)): ?>
<form method="post" action="<?= site_url('anuncios/'.$item['id'].'/restaurar') ?>"><?= csrf_field() ?><label><input type="checkbox" name="confirmar" value="1" required> Confirmo a restauração com a validade original.</label> <button class="pl-btn pl-contorno" type="submit">Restaurar</button></form>
<?php elseif ($acao === 'republicar' && !($somenteConsulta ?? false)): ?>
<a class="pl-btn pl-contorno" href="<?= site_url('anuncios/'.$item['id'].'/republicar') ?>">Republicar</a>
<?php else: ?><span>Operação indisponível</span><?php endif; ?>
</div></article>
<?php endforeach; ?>
    </div>
  </section>
  <section class="pl-perigo"><div><h2>Desativar loja</h2><p>A loja deixará de aparecer publicamente. Somente os anúncios desta loja serão excluídos logicamente; seus anúncios pessoais permanecerão ativos.</p></div><button class="pl-btn pl-vermelho" type="button" id="pl-abrir-desativacao">Desativar loja</button></section>
</main>
<dialog id="pl-modal-desativacao" class="pl-modal" aria-labelledby="pl-titulo-desativacao"><h2 id="pl-titulo-desativacao">Desativar esta loja?</h2><p>Os anúncios desta empresa serão excluídos logicamente e não voltarão automaticamente após uma reativação. Os anúncios pessoais não serão afetados.</p><form method="post" action="<?= site_url('lojas/' . $loja['id'] . '/desativar') ?>" id="pl-form-desativar"><?= csrf_field() ?><div class="pl-modal-acoes"><button type="button" class="pl-btn pl-contorno" data-fechar>Cancelar</button><button type="submit" class="pl-btn pl-vermelho">Sim, desativar</button></div></form></dialog>
<dialog id="pl-modal-excluir" class="pl-modal" aria-labelledby="pl-titulo-excluir">
  <h2 id="pl-titulo-excluir">Excluir anúncio?</h2>
  <p id="pl-anuncio-excluir"></p>
  <p>O anúncio deixará de aparecer publicamente.</p>
  <form method="post" id="pl-form-excluir">
    <?= csrf_field() ?>
    <div class="pl-modal-acoes">
      <button type="button" class="pl-btn pl-contorno" data-fechar>Cancelar</button>
      <button type="submit" class="pl-btn pl-vermelho" disabled>Sim, excluir</button>
    </div>
  </form>
</dialog>
</body></html>
