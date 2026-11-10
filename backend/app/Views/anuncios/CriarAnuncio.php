<?php

$usuario = $usuario ?? [];
$loja = $loja ?? null;

$cidadeUsuario = $cidadeUsuario ?? null;
$cidadeLoja = $cidadeLoja ?? null;

$temLoja = ! empty($loja);

$tipoInicial = old('tipo_anunciante', 'pessoa');

if ($tipoInicial !== 'loja' || ! $temLoja) {
    $tipoInicial = 'pessoa';
}

$nomeCidadePessoa = $cidadeUsuario
    ? $cidadeUsuario['nome'] . ' - ' . $cidadeUsuario['uf']
    : '';

$nomeCidadeLoja = $cidadeLoja
    ? $cidadeLoja['nome'] . ' - ' . $cidadeLoja['uf']
    : '';

$cidadePessoa = (string) ($usuario['cidade_id'] ?? '');
$cidadeLojaId = (string) ($loja['cidade_id'] ?? '');

$contatoPessoa = (string) ($usuario['contato_padrao'] ?? '');
$contatoLoja = (string) ($loja['contato'] ?? '');

$cidadePadrao = $tipoInicial === 'loja'
    ? $cidadeLojaId
    : $cidadePessoa;

$nomeCidadePadrao = $tipoInicial === 'loja'
    ? $nomeCidadeLoja
    : $nomeCidadePessoa;

$contatoPadrao = $tipoInicial === 'loja'
    ? $contatoLoja
    : $contatoPessoa;

$cidadeInicial = (string) old('cidade_id', $cidadePadrao);
$contatoInicial = (string) old('contato', $contatoPadrao);

$nomeCidadeInicial = (string) old(
    'cidade_busca',
    $nomeCidadePadrao
);

// Não associar um nome padrão a outro código IBGE
if (
    old('cidade_id') !== null
    && old('cidade_busca') === null
    && $cidadeInicial !== $cidadePadrao
) {
    $nomeCidadeInicial = '';
}

$erros = session('erros') ?? [];
$erroGeral = session('erro');
$sucesso = session('sucesso');

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar anúncio | BargainCar</title>

    <link
        rel="stylesheet"
        href="<?= base_url('css/CriarAnuncio.css') ?>"
    >
</head>

<body>

<main class="pagina-criar-anuncio">

    <div class="cabecalho-pagina">
        <h1>Criar anúncio</h1>

        <p>
            Preencha os dados do veículo e adicione suas mídias
            para publicar seu anúncio.
        </p>
    </div>

    <?php if ($erroGeral): ?>
        <div class="mensagem mensagem-erro" role="alert">
            <?= esc($erroGeral) ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="mensagem mensagem-sucesso" role="status">
            <?= esc($sucesso) ?>
        </div>
    <?php endif; ?>

    <?php if (! empty($erros)): ?>
        <div class="mensagem mensagem-erro" role="alert">
            <strong>Verifique os campos informados:</strong>

            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?= esc($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        id="formCriarAnuncio"
        action="<?= site_url('anuncios/salvar') ?>"
        method="post"
        enctype="multipart/form-data"

        data-cidade-pessoa="<?= esc($cidadePessoa, 'attr') ?>"
        data-cidade-loja="<?= esc($cidadeLojaId, 'attr') ?>"

        data-nome-cidade-pessoa="<?= esc($nomeCidadePessoa, 'attr') ?>"
        data-nome-cidade-loja="<?= esc($nomeCidadeLoja, 'attr') ?>"

        data-contato-pessoa="<?= esc($contatoPessoa, 'attr') ?>"
        data-contato-loja="<?= esc($contatoLoja, 'attr') ?>"

        data-url-cidades="<?= esc(site_url('cidades'), 'attr') ?>"
    >

        <?= csrf_field() ?>

        <div class="conteudo-anuncio">

            <div class="coluna-principal">

                <section class="card-formulario">

                    <h2>Informações do veículo</h2>

                    <div class="campo">
                        <label for="titulo">
                            Título do anúncio *
                        </label>

                        <input
                            type="text"
                            id="titulo"
                            name="titulo"
                            value="<?= esc(old('titulo', ''), 'attr') ?>"
                            placeholder="Ex.: Chevrolet Onix LT 1.0"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="linha-campos">

                        <div class="campo">
                            <label for="ano">Ano *</label>

                            <input
                                type="number"
                                id="ano"
                                name="ano"
                                value="<?= esc(old('ano', ''), 'attr') ?>"
                                min="1900"
                                max="2100"
                                placeholder="2020"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="preco">Preço (R$) *</label>

                            <input
                                type="number"
                                id="preco"
                                name="preco"
                                value="<?= esc(old('preco', ''), 'attr') ?>"
                                min="0.01"
                                step="0.01"
                                placeholder="45000.00"
                                required
                            >
                        </div>

                    </div>

                    <div class="campo">
                        <label for="cor">Cor *</label>

                        <input
                            type="text"
                            id="cor"
                            name="cor"
                            value="<?= esc(old('cor', ''), 'attr') ?>"
                            maxlength="50"
                            placeholder="Ex.: Branco"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="descricao">Descrição *</label>

                        <textarea
                            id="descricao"
                            name="descricao"
                            rows="5"
                            placeholder="Descreva as características do veículo."
                            required
                        ><?= esc(old('descricao', '')) ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="defeitos">
                            Defeitos conhecidos
                        </label>

                        <textarea
                            id="defeitos"
                            name="defeitos"
                            rows="3"
                            placeholder="Informe os defeitos conhecidos."
                        ><?= esc(old('defeitos', '')) ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="debitos_pendentes">
                            Débitos pendentes
                        </label>

                        <textarea
                            id="debitos_pendentes"
                            name="debitos_pendentes"
                            rows="3"
                            placeholder="Informe se existem débitos pendentes."
                        ><?= esc(old('debitos_pendentes', '')) ?></textarea>
                    </div>

                    <div class="campo">
                        <label for="situacao_documentacao">
                            Situação da documentação *
                        </label>

                        <select
                            id="situacao_documentacao"
                            name="situacao_documentacao"
                            required
                        >
                            <option value="">
                                Selecione
                            </option>

                            <?php
                            $situacoes = [
                                'regular' => 'Regular',
                                'pendente' => 'Com pendências',
                            ];

                            foreach ($situacoes as $valor => $rotulo):
                            ?>
                                <option
                                    value="<?= esc($valor, 'attr') ?>"
                                    <?= old('situacao_documentacao') === $valor ? 'selected' : '' ?>
                                >
                                    <?= esc($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </section>

                <section class="card-formulario">

                    <h2>Fotos e vídeo</h2>

                    <div class="campo">
                        <label for="fotos">
                            Fotos do veículo *
                        </label>

                        <input
                            type="file"
                            id="fotos"
                            name="fotos[]"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            multiple
                            required
                        >

                        <p class="ajuda">
                            Selecione de 3 a 5 fotos.
                            Formatos JPG, PNG ou WebP.
                            Máximo de 5 MB por foto.
                        </p>

                        <p id="contadorFotos" aria-live="polite">
                            Nenhuma foto selecionada.
                        </p>

                        <div
                            id="previewFotos"
                            class="preview-fotos"
                        ></div>
                    </div>

                    <div class="campo">
                        <label for="video">
                            Vídeo do veículo (opcional)
                        </label>

                        <input
                            type="file"
                            id="video"
                            name="video"
                            accept=".mp4,video/mp4"
                        >

                        <p class="ajuda">
                            Formato MP4. Máximo de 50 MB.
                        </p>

                        <p id="nomeVideo" aria-live="polite"></p>
                    </div>

                </section>

            </div>

            <aside class="coluna-lateral">

                <section class="card-formulario">

                    <h2>Dados do anunciante</h2>

                    <div class="campo">
                        <label>Publicar como *</label>

                        <div class="opcoes-anunciante">

                            <label>
                                <input
                                    type="radio"
                                    name="tipo_anunciante"
                                    value="pessoa"
                                    <?= $tipoInicial === 'pessoa' ? 'checked' : '' ?>
                                >
                                Pessoa física
                            </label>

                            <?php if ($temLoja): ?>
                                <label>
                                    <input
                                        type="radio"
                                        name="tipo_anunciante"
                                        value="loja"
                                        <?= $tipoInicial === 'loja' ? 'checked' : '' ?>
                                    >
                                    Loja
                                </label>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php if ($temLoja): ?>
                        <div
                            id="informacoesLoja"
                            class="informacoes-loja"
                            <?= $tipoInicial !== 'loja' ? 'hidden' : '' ?>
                        >
                            <strong>
                                <?= esc($loja['nome_empresa'] ?? '') ?>
                            </strong>

                            <p>
                                O anúncio será vinculado à sua loja.
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="campo campo-cidade">

                        <label for="cidade_busca">
                            Cidade do anúncio *
                        </label>

                        <input
                            type="text"
                            id="cidade_busca"
                            name="cidade_busca"
                            value="<?= esc($nomeCidadeInicial, 'attr') ?>"
                            placeholder="Digite o nome da cidade"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-controls="sugestoesCidades"
                            required
                        >

                        <input
                            type="hidden"
                            id="cidade_id"
                            name="cidade_id"
                            value="<?= esc($cidadeInicial, 'attr') ?>"
                        >

                        <div
                            id="sugestoesCidades"
                            class="sugestoes-cidades"
                            role="listbox"
                            hidden
                        ></div>

                        <p class="ajuda">
                            Digite pelo menos 2 letras
                            e selecione uma cidade.
                        </p>

                    </div>

                    <div class="campo">

                        <label for="contato">
                            Contato *
                        </label>

                        <input
                            type="text"
                            id="contato"
                            name="contato"
                            value="<?= esc($contatoInicial, 'attr') ?>"
                            maxlength="30"
                            placeholder="Telefone ou WhatsApp"
                            required
                        >

                    </div>

                </section>

                <section class="card-formulario">

                    <h2>Antes de publicar</h2>

                    <p>
                        Confira os dados do veículo e as mídias
                        selecionadas.
                    </p>

                    <p>
                        O anúncio terá validade de 30 dias.
                    </p>

                    <button
                        type="submit"
                        id="botaoPublicar"
                        class="botao-publicar"
                    >
                        Publicar anúncio
                    </button>

                </section>

            </aside>

        </div>

    </form>

</main>

<script src="<?= base_url('js/CriarAnuncio.js') ?>"></script>

</body>
</html>
