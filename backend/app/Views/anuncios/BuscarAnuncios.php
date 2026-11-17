<?php
$termo = $termo ?? '';
$cidade = $cidade ?? null;
$erro = $erro ?? null;
$anuncios = $anuncios ?? [];
$raioKm = $raioKm ?? 100;
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
                    Anúncios em até
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
                    Resultados para:
                    <strong><?= esc($termo) ?></strong>
                </p>
            <?php endif; ?>

        </section>

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

            <div class="busca-mensagem">
                <h2>Nenhum veículo encontrado</h2>
                <p>
                    Não encontramos anúncios que correspondam
                    à sua pesquisa dentro do raio de
                    <?= esc((string) $raioKm) ?> km.
                </p>
                <p>
                    Experimente pesquisar outro veículo
                    ou escolher outra cidade.
                </p>
            </div>

        <?php else: ?>

            <p class="busca-quantidade">
                <?= count($anuncios) ?>
                anúncio(s) encontrado(s)
            </p>

            <div class="busca-grade">

                <?php foreach ($anuncios as $anuncio): ?>

                    <a
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

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>
