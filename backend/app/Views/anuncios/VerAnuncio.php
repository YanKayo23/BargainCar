
<?php

/**
 * @var array $anuncio
 * @var array|null $cidade
 * @var array $fotos
 * @var array|null $video
 * @var string $nomeAnunciante
 * @var int|null $perfilId
 */

$titulo = (string) ($anuncio['titulo'] ?? '');
$preco = (float) ($anuncio['preco'] ?? 0);
$anuncioId = (int) ($anuncio['id'] ?? 0);

$tipoAnunciante = $anuncio['tipo_anunciante'] ?? 'pessoa';
$ehLoja = $tipoAnunciante === 'loja';

$tipoTexto = $ehLoja ? 'Loja' : 'Pessoa física';

$inicial = mb_strtoupper(
    mb_substr($nomeAnunciante, 0, 1)
);

$localizacao = $cidade['nome'] ?? 'Não informada';

if (! empty($cidade['uf'])) {
    $localizacao .= ' - ' . $cidade['uf'];
}

$contato = (string) ($anuncio['contato'] ?? '');
$numeros = preg_replace('/\D/', '', $contato);

$whatsapp = null;

if (strlen($numeros) === 11) {
    $whatsapp = '55' . $numeros;
} elseif (
    strlen($numeros) === 13
    && str_starts_with($numeros, '55')
) {
    $whatsapp = $numeros;
}

// Monta a galeria.
$midias = [];

foreach ($fotos as $foto) {
    $midias[] = [
        'tipo' => 'foto',
        'url' => site_url(
            'anuncios/' . $anuncioId
            . '/midias/' . $foto['id']
        ),
    ];
}

if (! empty($video)) {
    $midias[] = [
        'tipo' => 'video',
        'url' => site_url(
            'anuncios/' . $anuncioId
            . '/midias/' . $video['id']
        ),
    ];
}

$primeiraFoto = '';

foreach ($midias as $midia) {
    if ($midia['tipo'] === 'foto') {
        $primeiraFoto = $midia['url'];
        break;
    }
}

$situacoes = [
    'regular' => 'Regular',
    'pendente' => 'Com pendências',
];

$situacaoOriginal = $anuncio['situacao_documentacao']
    ?? 'Não informada';

$situacao = $situacoes[$situacaoOriginal]
    ?? $situacaoOriginal;

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= esc($titulo) ?> | BargainCar</title>

    <link
        rel="stylesheet"
        href="<?= base_url('css/VerAnuncio.css') ?>"
    >
</head>

<body>

<header class="topo">

    <div class="topo-conteudo">

        <a href="<?= site_url('/') ?>" class="marca">
            <span class="marca-icone">B</span>
            <span>BargainCar</span>
        </a>

        <nav
            class="navegacao"
            aria-label="Navegação principal"
        >

            <a href="<?= site_url('/') ?>">Buscar</a>

            <span class="nav-futuro">Favoritos</span>

            <span class="nav-futuro">Meu perfil</span>

            <span class="tipo-conta">Pessoa</span>

            <a
                href="<?= site_url('criar-anuncio') ?>"
                class="botao-anunciar"
            >
                + Anunciar
            </a>

        </nav>

    </div>

</header>

<main class="container">

    <a href="<?= site_url('/') ?>" class="voltar">
        ← Voltar
    </a>

    <div class="layout-superior">

        <!-- GALERIA -->

        <section
            class="galeria"
            aria-label="Fotos do veículo"
        >

            <div class="imagem-principal">

                <?php if ($primeiraFoto !== ''): ?>

                    <img
                        id="fotoPrincipal"
                        src="<?= esc($primeiraFoto, 'attr') ?>"
                        alt="<?= esc($titulo, 'attr') ?>"
                    >

                    <video
                        id="videoPrincipal"
                        controls
                        preload="metadata"
                        playsinline
                        hidden
                    ></video>

                <?php else: ?>

                    <div class="sem-imagem">
                        Nenhuma foto disponível.
                    </div>

                <?php endif; ?>

            </div>

            <?php if (! empty($midias)): ?>

                <div class="miniaturas">

                    <?php foreach ($midias as $indice => $midia): ?>

                        <button
                            type="button"
                            class="miniatura <?= $indice === 0 ? 'ativa' : '' ?>"
                            data-tipo="<?= esc($midia['tipo'], 'attr') ?>"
                            data-url="<?= esc($midia['url'], 'attr') ?>"
                            aria-label="Visualizar mídia <?= $indice + 1 ?>"
                            aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>"
                        >

                            <?php if ($midia['tipo'] === 'foto'): ?>

                                <img
                                    src="<?= esc($midia['url'], 'attr') ?>"
                                    alt="Foto <?= $indice + 1 ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <span class="icone-video">▶</span>
                                <span class="legenda-video">Vídeo</span>

                            <?php endif; ?>

                        </button>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

        <!-- INFORMAÇÕES PRINCIPAIS -->

        <aside class="cartao resumo-anuncio">

            <div class="etiquetas-anuncio">

                <span class="etiqueta-ativo">
                    Ativo
                </span>

                <span class="etiqueta-tipo">
                    <?= esc($tipoTexto) ?>
                </span>

            </div>

            <h1><?= esc($titulo) ?></h1>

            <div class="preco">
                R$ <?= number_format($preco, 2, ',', '.') ?>
            </div>

            <div class="linha-informacao">
                <span>Ano</span>
                <strong><?= esc($anuncio['ano']) ?></strong>
            </div>

            <div class="linha-informacao">
                <span>Cor</span>
                <strong><?= esc($anuncio['cor']) ?></strong>
            </div>

            <div class="linha-informacao">
                <span>Cidade</span>
                <strong><?= esc($localizacao) ?></strong>
            </div>

            <!-- PERFIL DO ANUNCIANTE -->

            <div class="identificacao-anunciante">

                <p class="rotulo-anunciante">
                    Anunciado por
                </p>

                <div class="perfil-anunciante">

                    <div class="foto-anunciante">

                        <span>
                            <?= esc($inicial) ?>
                        </span>

                    </div>

                    <div class="dados-anunciante">

                        <strong>
                            <?= esc($nomeAnunciante) ?>
                        </strong>

                        <span>
                            <?= esc($tipoTexto) ?>
                        </span>

                    </div>

                    <span
                        class="seta-perfil"
                        aria-hidden="true"
                    >
                        ›
                    </span>

                </div>

                <p class="aviso-perfil">
                    Visualização do perfil em breve.
                </p>

            </div>

            <!-- CONTATO -->

            <button
                type="button"
                id="mostrarContato"
                class="botao botao-principal"
                aria-expanded="false"
                aria-controls="dadosContato"
            >
                Ver contato do vendedor
            </button>

            <div
                id="dadosContato"
                class="dados-contato"
                hidden
            >

                <?php if ($contato !== ''): ?>

                    <strong>
                        <?= esc($contato) ?>
                    </strong>

                    <?php if ($whatsapp): ?>

                        <a
                            href="https://wa.me/<?= esc($whatsapp, 'attr') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="botao botao-whatsapp"
                        >
                            Conversar no WhatsApp
                        </a>

                    <?php endif; ?>

                <?php else: ?>

                    <p>Contato não informado.</p>

                <?php endif; ?>

            </div>

            <button
                type="button"
                class="botao botao-favorito"
                disabled
                title="Funcionalidade em desenvolvimento"
            >
                ♡ Favoritar (em breve)
            </button>

        </aside>

    </div>

    <!-- SOBRE O VEÍCULO -->

    <section class="cartao sobre-veiculo">

        <h2>Sobre o veículo</h2>

        <div class="detalhe">
            <h3>Descrição</h3>

            <p>
                <?= nl2br(esc(
                    $anuncio['descricao'] ?? ''
                )) ?>
            </p>
        </div>

        <div class="detalhe">
            <h3>Defeitos</h3>

            <p>
                <?= nl2br(esc(
                    $anuncio['defeitos']
                    ?: 'Nenhum defeito informado.'
                )) ?>
            </p>
        </div>

        <div class="detalhe">
            <h3>Débitos pendentes</h3>

            <p>
                <?= nl2br(esc(
                    $anuncio['debitos_pendentes']
                    ?: 'Nenhum débito informado.'
                )) ?>
            </p>
        </div>

        <div class="detalhe">
            <h3>Situação da documentação</h3>

            <p><?= esc($situacao) ?></p>
        </div>

    </section>

    <!-- PERGUNTAS -->

    <section class="cartao perguntas">

        <h2>Perguntas ao vendedor</h2>

        <div class="formulario-pergunta">

            <input
                type="text"
                placeholder="Escreva sua pergunta"
                aria-label="Escreva sua pergunta"
                disabled
            >

            <button
                type="button"
                class="botao botao-principal"
                disabled
            >
                Perguntar
            </button>

        </div>

        <p class="mensagem-perguntas">
            Em breve você poderá enviar perguntas públicas
            ao vendedor e acompanhar as respostas aqui.
        </p>

    </section>

</main>

<footer class="rodape">
    BargainCar — Compre e venda carros perto de você.
</footer>

<script src="<?= base_url('js/VerAnuncio.js') ?>"></script>

</body>
</html>
