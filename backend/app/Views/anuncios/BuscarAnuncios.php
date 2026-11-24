<?php
$termo = $termo ?? '';
$cidade = $cidade ?? null;
$erro = $erro ?? null;
$anuncios = $anuncios ?? [];
$lojas = $lojas ?? [];
$raioKm = (int) ($raioKm ?? 100);
$cidadeIdFiltro = (string) ($cidadeIdFiltro ?? '');
$proximaDistancia = $proximaDistancia ?? null;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Buscar anúncios | BargainCar</title>

    <link
        rel="stylesheet"
        href="<?= base_url('css/BuscarAnuncios.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/Favoritos.css') ?>">
    <script defer src="<?= base_url('js/Favoritos.js') ?>"></script>
</head>

<body>

    <!-- Navbar compartilhada do BargainCar -->
    <?= view('layouts/Navbar') ?>

    <main class="busca-container">

        <a href="<?= site_url('/') ?>" class="busca-voltar">
            ← Voltar
        </a>

        <section class="busca-cabecalho">

            <h1>Veículos disponíveis</h1>

            <?php if ($cidade): ?>
                <p>
                    Anúncios e lojas em até
                    <strong><?= esc((string) $raioKm) ?> km</strong>
                    de
                    <strong>
                        <?= esc($cidade['nome']) ?>
                        - <?= esc($cidade['uf']) ?>
                    </strong>
                </p>
            <?php endif; ?>

            <?php if ($termo !== ''): ?>
                <p>
                    Veículos pesquisados:
                    <strong><?= esc($termo) ?></strong>
                </p>
            <?php endif; ?>

        </section>

        <?php if (!$erro && $cidade): ?>
            <section class="busca-lojas" aria-labelledby="busca-lojas-titulo">
                <h2 id="busca-lojas-titulo">Lojas próximas</h2>
                <?php if (empty($lojas)): ?>
                    <p class="busca-lojas-vazio">Nenhuma loja encontrada nesse raio.</p>
                <?php else: ?>
                    <div class="busca-lojas-grade">
                        <?php foreach ($lojas as $loja): ?>
                            <a class="busca-loja-cartao" href="<?= site_url('lojas/' . (int) $loja['id']) ?>">
                                <div class="busca-loja-foto">
                                    <?php if (!empty($loja['foto_perfil']) && is_file(FCPATH . $loja['foto_perfil'])): ?>
                                        <img src="<?= esc(base_url($loja['foto_perfil']), 'attr') ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 9l2-6h14l2 6v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0z"/>
                                            <path d="M5 14v7h14v-7M10 21v-6h4v6"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <h3><?= esc($loja['nome_empresa']) ?></h3>
                                <p><?= esc($loja['cidade_nome']) ?> - <?= esc($loja['cidade_uf']) ?></p>
                                <p class="busca-loja-distancia"><?= number_format((float) $loja['distancia_km'], 1, ',', '.') ?> km de distância aproximada</p>
                                <span class="busca-loja-botao">Ver loja</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <h2 class="busca-anuncios-titulo">Anúncios encontrados</h2>
        <?php endif; ?>

        <?php if ($erro): ?>

            <div class="busca-mensagem">
                <h2>Escolha uma cidade</h2>
                <p><?= esc((string) $erro) ?></p>
                <p>
                    Utilize a barra de pesquisa acima
                    para selecionar uma cidade.
                </p>
            </div>

        <?php elseif (empty($anuncios)): ?>

            <div class="busca-mensagem" role="status">
                <h2>Nenhum anúncio encontrado</h2>

                <?php if ($proximaDistancia !== null): ?>
                    <p>Nenhum anúncio encontrado nesse raio. Deseja ampliar a distância?</p>
                    <form class="busca-ampliar-form" method="get" action="<?= site_url('buscar') ?>">
                        <input type="hidden" name="termo" value="<?= esc((string) $termo, 'attr') ?>">
                        <input type="hidden" name="cidade_id" value="<?= esc($cidadeIdFiltro, 'attr') ?>">
                        <input type="hidden" name="distancia" value="<?= (int) $proximaDistancia ?>">
                        <button class="busca-ampliar-botao" type="submit">
                            Ampliar para <?= (int) $proximaDistancia ?> km
                        </button>
                    </form>
                    <p class="busca-mensagem-apoio">A cidade e o título da pesquisa serão mantidos.</p>
                <?php else: ?>
                    <p>Nenhum anúncio encontrado em até 100 km. Tente outra cidade ou outro termo de pesquisa.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <p class="busca-quantidade">
                <?= count($anuncios) ?>
                anúncio(s) encontrado(s)
            </p>

            <div class="busca-grade">

                <?php foreach ($anuncios as $anuncio): ?>

                    <div class="bc-fav-wrap"><a
                        class="busca-cartao"
                        href="<?= site_url(
                                    'anuncios/' . $anuncio['id']
                                ) ?>">


                        <div class="busca-foto">

                            <?php if (!empty($anuncio['foto_id'])): ?>

                                <img
                                    src="<?= site_url(
                                                'anuncios/'
                                                    . (int) $anuncio['id']
                                                    . '/midias/'
                                                    . (int) $anuncio['foto_id']
                                            ) ?>"
                                    alt="<?= esc('Foto do veículo ' . $anuncio['titulo'], 'attr') ?>"
                                    loading="lazy">

                            <?php else: ?>

                                <span>Foto indisponível</span>

                            <?php endif; ?>

                        </div>


                        <div class="busca-cartao-conteudo">

                            <span class="busca-status">
                                <?= esc(
                                    ucfirst($anuncio['status'])
                                ) ?>
                            </span>

                            <h2>
                                <?= esc($anuncio['titulo']) ?>
                            </h2>

                            <strong class="busca-preco">
                                R$
                                <?= number_format(
                                    (float) $anuncio['preco'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>

                            <p>
                                Ano:
                                <?= esc($anuncio['ano']) ?>
                            </p>

                            <p>
                                <?= esc($anuncio['cidade_nome']) ?>
                                - <?= esc($anuncio['cidade_uf']) ?>
                            </p>

                            <?php if (
                                $anuncio['distancia_km'] !== null
                            ): ?>

                                <p class="busca-distancia">
                                    Aproximadamente
                                    <?= number_format(
                                        (float) $anuncio['distancia_km'],
                                        1,
                                        ',',
                                        '.'
                                    ) ?>
                                    km de distância
                                </p>

                            <?php endif; ?>

                        </div>

                    </a>
<?= view('layouts/CoracaoFavorito',['anuncioId'=>$anuncio['id']]) ?>
</div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>
