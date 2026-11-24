<?php
/** @var array $anuncio */
/** @var array|null $cidade */
/** @var array $fotos */
/** @var array|null $video */
$republicar = $republicar ?? false;
$erros = session('erros') ?? [];
$erroGeral = session('erro');
$sucesso = session('sucesso');
$nomeCidade = $cidade ? $cidade['nome'] . ' - ' . $cidade['uf'] : '';
$nomeCidadeInicial = old('cidade_busca', $nomeCidade);
$cidadeInicial = old('cidade_id', (string) $anuncio['cidade_id']);
$contatoInicial = old('contato', (string) $anuncio['contato']);
$removidas = old('remover_fotos', []);
$removidas = is_array($removidas) ? array_map('strval', $removidas) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $republicar ? 'Republicar' : 'Editar' ?> anúncio | BargainCar</title>

    <link rel="stylesheet" href="<?= base_url('css/CriarAnuncio.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/EditarAnuncio.css') ?>">
</head>

<body>

<?= view('layouts/Navbar') ?>
<main class="pagina-criar-anuncio">

    <a class="voltar" href="<?= esc($urlRetorno, 'attr') ?>">← Voltar</a>
    <div class="cabecalho-pagina">
        <h1><?= $republicar ? 'Republicar anúncio' : 'Editar anúncio' ?></h1>

        <p>
            <?= $republicar ? 'Revise os dados e mídias. Uma nova publicação terá validade de 30 dias.' : 'Atualize os dados e as mídias do anúncio. O prazo de validade não será reiniciado.' ?>
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
        id="formEditarAnuncio"
        action="<?= site_url('anuncios/' . $anuncio['id'] . ($republicar ? '/republicar' : '/atualizar')) ?>"
        method="post"
        enctype="multipart/form-data"

        data-url-cidades="<?= esc(site_url('cidades'), 'attr') ?>"
        data-fotos-existentes="<?= count($fotos) ?>"
    >

        <?= csrf_field() ?>
        <input type="hidden" name="origem" value="<?= esc($origem, 'attr') ?>">

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
                            value="<?= esc(old('titulo', $anuncio['titulo'] ?? ''), 'attr') ?>"
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
                                value="<?= esc(old('ano', $anuncio['ano'] ?? ''), 'attr') ?>"
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
                                value="<?= esc(old('preco', $anuncio['preco'] ?? ''), 'attr') ?>"
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
                            value="<?= esc(old('cor', $anuncio['cor'] ?? ''), 'attr') ?>"
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
                        ><?= esc(old('descricao', $anuncio['descricao'] ?? '')) ?></textarea>
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
                        ><?= esc(old('defeitos', $anuncio['defeitos'] ?? '')) ?></textarea>
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
                        ><?= esc(old('debitos_pendentes', $anuncio['debitos_pendentes'] ?? '')) ?></textarea>
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
                                    <?= old('situacao_documentacao', $anuncio['situacao_documentacao']) === $valor ? 'selected' : '' ?>
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
                        <label>Fotos atuais (marque as que deseja remover)</label>
                        <div class="fotos-atuais" id="fotosAtuais">
                            <?php foreach ($fotos as $foto): ?>
                                <label class="foto-atual">
                                    <img src="<?= esc(site_url('anuncios/' . $anuncio['id'] . ($republicar ? '/midias-historico/' : '/midias-edicao/') . $foto['id']), 'attr') ?>"
                                         alt="Foto atual do veículo">
                                    <span>
                                        <input type="checkbox" name="remover_fotos[]"
                                            value="<?= (int) $foto['id'] ?>"
                                            <?= in_array((string) $foto['id'], $removidas, true) ? 'checked' : '' ?>>
                                        Remover foto
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label for="fotos">Adicionar novas fotos (opcional)</label>
                        <input type="file" id="fotos" name="fotos[]"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                        <p class="ajuda">Mantenha de 3 a 5 fotos no total. JPG, PNG ou WebP; até 5 MB por foto.</p>
                        <p id="contadorFotos" aria-live="polite"></p>
                        <div id="previewFotos" class="preview-fotos"></div>
                    </div>

                    <div class="campo">
                        <label for="video">
                            Substituir vídeo (opcional)
                        </label>

                        <input
                            type="file"
                            id="video"
                            name="video"
                            accept=".mp4,video/mp4"
                        >

                        <?php if ($video): ?>
                            <p>Vídeo atual: <a target="_blank" rel="noopener"
                                href="<?= esc(site_url('anuncios/' . $anuncio['id'] . ($republicar ? '/midias-historico/' : '/midias-edicao/') . $video['id']), 'attr') ?>">Visualizar vídeo</a></p>
                            <label class="opcao-video"><input type="checkbox" name="remover_video" value="1"
                                <?= old('remover_video') === '1' ? 'checked' : '' ?>> Remover vídeo atual</label>
                        <?php endif; ?>
                        <p class="ajuda">Se selecionar um novo vídeo, ele substituirá o anterior. MP4, até 50 MB.</p>

                        <p id="nomeVideo" aria-live="polite"></p>
                    </div>

                </section>

            </div>

            <aside class="coluna-lateral">

                <section class="card-formulario">

                    <h2>Dados do anunciante</h2>

                    <p class="ajuda">Anúncio publicado como pessoa física.</p>

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

                    <h2>Antes de salvar</h2>

                    <p>
                        Confira os dados do veículo e as mídias
                        selecionadas.
                    </p>

                    <p>
                        <?= $republicar ? 'O original permanecerá no histórico.' : 'A edição não altera a data de expiração do anúncio.' ?>
                    </p>

                    <?php if ($republicar): ?><label class="opcao-video"><input type="checkbox" name="confirmar" value="1" required> Confirmo a criação de um novo anúncio.</label><?php endif; ?>
                    <button
                        type="submit"
                        id="botaoSalvar"
                        class="botao-publicar"
                    >
                        <?= $republicar ? 'Republicar anúncio' : 'Salvar alterações' ?>
                    </button>
                    <a class="voltar" href="<?= esc($urlRetorno, 'attr') ?>">Cancelar</a>

                </section>

            </aside>

        </div>

    </form>

</main>

<script src="<?= base_url('js/EditarAnuncio.js') ?>"></script>

</body>
</html>
