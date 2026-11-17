<?php
/** @var array|null $cidade */
/** @var array $anuncios */
/** @var string|null $erro */
$cidade = $cidade ?? null;
$anuncios = $anuncios ?? [];
$erro = $erro ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Início | BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/Home.css') ?>">
    <script src="<?= base_url('js/Home.js') ?>" defer></script>
</head>
<body>
    <?= view('layouts/Navbar') ?>

    <main class="home-container">
        <?php if ($mensagem = session()->getFlashdata('sucesso')): ?>
            <div class="home-mensagem home-sucesso" role="status"><?= esc($mensagem) ?></div>
        <?php endif; ?>
        <?php if ($mensagem = session()->getFlashdata('erro')): ?>
            <div class="home-mensagem home-erro" role="alert"><?= esc($mensagem) ?></div>
        <?php endif; ?>

        <section class="home-hero">
            <div>
                <span class="home-selo">BargainCar · anúncios da sua região</span>
                <h1>Encontre seu próximo carro perto de você.</h1>
                <p>Veja veículos anunciados na sua cidade e em municípios a até 100 km de distância.</p>
                <?php if ($cidade): ?>
                    <div class="home-local-atual">
                        <span aria-hidden="true">⌖</span>
                        <strong><?= esc($cidade['nome'] . ' - ' . $cidade['uf']) ?></strong>
                        <span>· raio de <?= (int) $raioKm ?> km</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="home-hero-icone" aria-hidden="true">🚘</div>
        </section>

        <section class="home-regiao" aria-labelledby="titulo-regiao">
            <div class="home-secao-cabecalho">
                <div>
                    <h2 id="titulo-regiao">Sua região</h2>
                    <p>Escolha outra cidade para ver os anúncios próximos dela.</p>
                </div>
            </div>
            <form class="home-form-cidade" id="home-form-cidade"
                  action="<?= site_url('/') ?>" method="get"
                  data-url-cidades="<?= esc(site_url('cidades'), 'attr') ?>">
                <div class="home-cidade-campo">
                    <label for="home-cidade-busca">Cidade de referência</label>
                    <input id="home-cidade-busca" type="text" autocomplete="off"
                           placeholder="Digite o nome da cidade"
                           value="<?= esc($cidade ? $cidade['nome'] . ' - ' . $cidade['uf'] : '', 'attr') ?>"
                           aria-controls="home-cidade-sugestoes" aria-autocomplete="list">
                    <input id="home-cidade-id" type="hidden" name="cidade_id"
                           value="<?= esc($cidade['codigo_ibge'] ?? '', 'attr') ?>">
                    <div id="home-cidade-sugestoes" class="home-sugestoes" role="listbox" hidden></div>
                </div>
                <button type="submit">Ver anúncios</button>
            </form>
            <?php if ($erro): ?>
                <p class="home-erro-texto" role="alert"><?= esc($erro) ?></p>
            <?php endif; ?>
        </section>

        <section class="home-listagem" aria-labelledby="titulo-anuncios">
            <div class="home-secao-cabecalho">
                <div>
                    <h2 id="titulo-anuncios">Anúncios da sua região</h2>
                    <p>
                        <?php if ($cidade): ?>
                            <?= count($anuncios) ?> anúncio(s) encontrado(s) em até <?= (int) $raioKm ?> km de <?= esc($cidade['nome']) ?>.
                        <?php else: ?>
                            Selecione uma cidade para encontrar veículos próximos.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if (! $cidade): ?>
                <div class="home-vazio">
                    <span aria-hidden="true">⌖</span>
                    <h3>Onde você deseja procurar?</h3>
                    <p>Selecione uma cidade acima para carregar anúncios da região.</p>
                </div>
            <?php elseif ($anuncios === []): ?>
                <div class="home-vazio">
                    <span aria-hidden="true">🚗</span>
                    <h3>Nenhum anúncio encontrado nesta região</h3>
                    <p>Experimente escolher outra cidade ou volte mais tarde.</p>
                </div>
            <?php else: ?>
                <div class="home-grid">
                    <?php foreach ($anuncios as $anuncio): ?>
                        <a class="home-anuncio" href="<?= site_url('anuncios/' . (int) $anuncio['id']) ?>">
                            <div class="home-anuncio-foto">
                                <?php if ($anuncio['foto_id']): ?>
                                    <img loading="lazy"
                                         src="<?= site_url('anuncios/' . (int) $anuncio['id'] . '/midias/' . (int) $anuncio['foto_id']) ?>"
                                         alt="Foto do veículo <?= esc($anuncio['titulo'], 'attr') ?>">
                                <?php else: ?>
                                    <div class="home-sem-foto" aria-label="Sem foto disponível">🚗</div>
                                <?php endif; ?>
                                <span class="home-tipo"><?= $anuncio['tipo_anunciante'] === 'loja' ? 'Loja' : 'Pessoa' ?></span>
                            </div>
                            <div class="home-anuncio-dados">
                                <h3><?= esc($anuncio['titulo']) ?></h3>
                                <strong class="home-preco">R$ <?= number_format((float) $anuncio['preco'], 2, ',', '.') ?></strong>
                                <p class="home-ano">Ano <?= esc((string) $anuncio['ano']) ?></p>
                                <?php if ($anuncio['tipo_anunciante'] === 'loja' && ! empty($anuncio['nome_loja'])): ?>
                                    <p class="home-loja"><?= esc($anuncio['nome_loja']) ?></p>
                                <?php endif; ?>
                                <div class="home-anuncio-rodape">
                                    <span>⌖ <?= esc($anuncio['cidade_nome'] . ' - ' . $anuncio['cidade_uf']) ?></span>
                                    <span><?= number_format((float) $anuncio['distancia_km'], 0, ',', '.') ?> km</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
