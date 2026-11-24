<link rel="stylesheet" href="<?= base_url('css/Perguntas.css') ?>">
<section class="bc-perguntas" id="bc-perguntas" data-url="<?= esc(site_url('anuncios/'.(int)$anuncioId.'/perguntas'),'attr') ?>">
<h2>Perguntas ao anunciante</h2>
<p>Tire suas dúvidas sobre este veículo. As perguntas e respostas são públicas.</p>
<div id="bc-perguntas-conteudo" aria-busy="true">Carregando perguntas...</div>
<p id="bc-perguntas-aviso" role="status"></p>
</section>
<script defer src="<?= base_url('js/Perguntas.js') ?>"></script>
