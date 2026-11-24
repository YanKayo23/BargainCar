<?php
/** @var array $loja @var array $anuncios @var array $fotos */
$nome = $loja['nome_empresa'];
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));
$cadastro = !empty($loja['data_cadastro']) ? new DateTimeImmutable($loja['data_cadastro']) : null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($nome) ?> | BargainCar</title>
<link rel="stylesheet" href="<?= base_url('css/VerPerfilLoja.css') ?>">
<script src="<?= base_url('js/VerPerfilLoja.js') ?>" defer></script>
</head>
<body>
<?= view('layouts/Navbar') ?>
<main class="vp-container">
<a class="vp-voltar" href="<?= site_url('/') ?>">← Voltar à Home</a>
<section class="vp-painel">
<div class="vp-capa">
<?php if (!empty($loja['foto_capa'])): ?>
<img src="<?= esc(base_url($loja['foto_capa']), 'attr') ?>" alt="Capa da loja">
<?php else: ?><span>Loja de veículos</span><?php endif; ?>
</div>
<div class="vp-info">
<div class="vp-avatar">
<?php if (!empty($loja['foto_perfil'])): ?>
<img src="<?= esc(base_url($loja['foto_perfil']), 'attr') ?>" alt="Foto da loja">
<?php else: ?><?= esc($inicial) ?><?php endif; ?>
</div>
<div><h1><?= esc($nome) ?></h1>
<p><?= $cadastro ? 'Na plataforma desde ' . esc($cadastro->format('m/Y')) : 'Loja BargainCar' ?></p></div>
</div>
</section>
<section class="vp-painel vp-anuncios">
<h2>Anúncios da loja</h2>
<p>Confira os veículos disponíveis nesta loja.</p>
<div class="vp-topo"><span><?= count($anuncios) ?> anúncio(s) disponível(is)</span>
<?php if ($anuncios): ?><label>Ordenar por <select id="vp-ordem"><option value="recentes">Mais recentes</option><option value="menor">Menor preço</option><option value="maior">Maior preço</option></select></label><?php endif; ?></div>
<?php if (!$anuncios): ?><p class="vp-vazio">Esta loja não possui anúncios disponíveis no momento.</p><?php endif; ?>
<div class="vp-grid" id="vp-grid">
<?php foreach ($anuncios as $a): ?>
<article class="vp-card" data-id="<?= (int)$a['id'] ?>" data-preco="<?= esc((string)$a['preco'], 'attr') ?>">
<div class="vp-foto">
<?php if (isset($fotos[$a['id']])): ?>
<img loading="lazy" src="<?= site_url('anuncios/' . $a['id'] . '/midias/' . $fotos[$a['id']]) ?>" alt="<?= esc($a['titulo'], 'attr') ?>">
<?php else: ?><span>Sem foto disponível</span><?php endif; ?>
</div>
<div class="vp-card-info"><h3><?= esc($a['titulo']) ?></h3><strong>R$ <?= number_format((float)$a['preco'], 2, ',', '.') ?></strong><p><?= esc($a['ano']) ?> · <?= esc($a['cor']) ?></p>
<a class="vp-ver" href="<?= site_url('anuncios/' . $a['id']) ?>">Ver anúncio</a></div>
</article>
<?php endforeach; ?>
</div>
</section>
</main>
</body>
</html>
