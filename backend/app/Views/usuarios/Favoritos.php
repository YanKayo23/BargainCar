<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Favoritos | BargainCar</title><link rel="stylesheet" href="<?= base_url('css/Favoritos.css') ?>"><script defer src="<?= base_url('js/Favoritos.js') ?>"></script></head><body>
<?= view('layouts/Navbar') ?>
<main class="bc-fav-pagina">
<a href="<?= site_url('/') ?>">← Voltar</a>
<h1>Meus favoritos</h1><p>Acompanhe os veículos que você salvou.</p>
<p id="bc-fav-total" aria-live="polite"><?= count($favoritos) ?> anúncio(s) salvo(s)</p>
<div class="bc-fav-grade">
<?php foreach ($favoritos as $a): ?>
<article class="bc-fav-card" data-fav-card>
<div class="bc-fav-foto">
<?php if ($a['disponivel'] && $a['foto_id']): ?><img loading="lazy" src="<?= site_url('anuncios/'.$a['id'].'/midias/'.$a['foto_id']) ?>" alt="<?= esc($a['titulo'],'attr') ?>"><?php else: ?><span><?= $a['disponivel']?'Sem foto':'Anúncio indisponível' ?></span><?php endif; ?>
<?= view('layouts/CoracaoFavorito',['anuncioId'=>$a['id']]) ?>
</div><div class="bc-fav-info">
<?php if ($a['disponivel']): ?>
<span class="bc-fav-tipo"><?= $a['tipo_anunciante'] === 'loja' ? 'Loja' : 'Pessoa' ?></span>
<h2><?= esc($a['titulo']) ?></h2><strong>R$ <?= number_format((float)$a['preco'],2,',','.') ?></strong><p>Ano: <?= esc($a['ano']) ?></p><p>Cor: <?= esc($a['cor']) ?></p><p><?= esc($a['cidade_nome'].' - '.$a['cidade_uf']) ?></p><a href="<?= site_url('anuncios/'.$a['id']) ?>">Ver anúncio</a>
<?php else: ?><h2>Anúncio indisponível</h2><p>Este anúncio não está mais disponível. Você pode removê-lo pelo coração.</p><?php endif; ?>
</div></article>
<?php endforeach; ?>
</div><div id="bc-fav-vazio" class="bc-fav-vazio" <?= $favoritos?'hidden':'' ?>><h2>Nenhum favorito salvo</h2><p>Clique no coração dos anúncios para salvá-los aqui.</p><a href="<?= site_url('/') ?>">Explorar anúncios</a></div>
</main></body></html>
