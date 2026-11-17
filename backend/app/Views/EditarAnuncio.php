
<?php
/**
 * BargainCar - Editar anúncio
 *
 * Dados recebidos do AnunciosController::editar()
 *
 * @var array $anuncio
 * @var array|null $cidade
 * @var array $fotos
 * @var array|null $video
 */
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar anúncio | BargainCar</title>

    <link rel="stylesheet"
          href="<?= base_url('css/CriarAnuncio.css') ?>">
</head>

<body>

    <main class="pagina-criar-anuncio">

        <div class="cabecalho-pagina">
            <h1>Editar anúncio</h1>

            <p>
                Atualize as informações do veículo
                e confira as mídias cadastradas.
            </p>
        </div>

        <section class="card-formulario">

            <h2>Informações atuais</h2>

            <p>
                <strong>Título:</strong>
                <?= esc($anuncio['titulo']) ?>
            </p>

            <p>
                <strong>Ano:</strong>
                <?= esc($anuncio['ano']) ?>
            </p>

            <p>
                <strong>Preço:</strong>
                R$ <?= number_format(
                    (float) $anuncio['preco'],
                    2,
                    ',',
                    '.'
                ) ?>
            </p>

            <p>
                <strong>Cidade:</strong>
                <?= esc($cidade['nome'] ?? 'Não encontrada') ?>
            </p>

            <p>
                <strong>Fotografias cadastradas:</strong>
                <?= count($fotos) ?>
            </p>

            <p>
                <strong>Vídeo:</strong>
                <?= $video ? 'Cadastrado' : 'Não cadastrado' ?>
            </p>

            <a href="<?= site_url('meu-perfil') ?>"
               class="botao secundario">
                Voltar ao meu perfil
            </a>

        </section>

    </main>

</body>
</html>
