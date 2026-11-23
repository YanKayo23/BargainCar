<?php
/** @var array $loja */
/** @var array|null $cidade */
$loja = $loja ?? [];
$cidade = $cidade ?? null;
$erros = session()->getFlashdata('erros') ?? [];
$erroGeral = session()->getFlashdata('erro');
$sucesso = session()->getFlashdata('sucesso');
$nomeCidade = $cidade ? $cidade['nome'] . ' - ' . $cidade['uf'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar dados da loja | BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/EditarLoja.css') ?>">
    <script src="<?= base_url('js/EditarLoja.js') ?>" defer></script>
</head>
<body>
    <?= view('layouts/Navbar') ?>
    <main class="el-container">
        <a href="<?= site_url('perfil-loja') ?>" class="el-voltar">← Voltar para minha loja</a>
        <section class="el-card">
            <h1>Editar dados da loja</h1>
            <p class="el-descricao">Atualize as informações da loja e escolha novas imagens de perfil e capa.</p>
            <?php if ($sucesso): ?>
                <div class="el-sucesso" role="status"><?= esc($sucesso) ?></div>
            <?php endif; ?>
            <?php if ($erroGeral || $erros): ?>
                <div class="el-erros" role="alert">
                    <?php if ($erroGeral): ?><p><?= esc($erroGeral) ?></p><?php endif; ?>
                    <?php if ($erros): ?><ul><?php foreach ($erros as $erro): ?><li><?= esc($erro) ?></li><?php endforeach; ?></ul><?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="el-fotos">
                <section class="el-foto-card" aria-labelledby="el-titulo-perfil">
                    <h2 id="el-titulo-perfil">Foto de perfil da loja</h2>
                    <div class="el-previa el-previa-perfil">
                        <img id="el-previa-img-perfil" src="<?= !empty($loja['foto_perfil']) ? esc(base_url($loja['foto_perfil']), 'attr') : '' ?>" alt="Prévia da foto de perfil" <?= empty($loja['foto_perfil']) ? 'hidden' : '' ?>>
                        <span id="el-sem-foto-perfil" <?= !empty($loja['foto_perfil']) ? 'hidden' : '' ?>>Sem foto</span>
                    </div>
                    <form method="post" action="<?= site_url('perfil-loja/imagem/perfil') ?>" enctype="multipart/form-data" class="el-form-imagem">
                        <?= csrf_field() ?>
                        <label for="el-imagem-perfil">Escolher foto de perfil</label>
                        <input id="el-imagem-perfil" name="imagem" type="file" accept="image/jpeg,image/png,image/webp" required data-previa="el-previa-img-perfil" data-vazio="el-sem-foto-perfil">
                        <button class="el-btn el-salvar" type="submit">Salvar foto de perfil</button>
                    </form>
                </section>
                <section class="el-foto-card" aria-labelledby="el-titulo-capa">
                    <h2 id="el-titulo-capa">Foto de fundo (capa)</h2>
                    <div class="el-previa el-previa-capa">
                        <img id="el-previa-img-capa" src="<?= !empty($loja['foto_capa']) ? esc(base_url($loja['foto_capa']), 'attr') : '' ?>" alt="Prévia da foto de capa" <?= empty($loja['foto_capa']) ? 'hidden' : '' ?>>
                        <span id="el-sem-foto-capa" <?= !empty($loja['foto_capa']) ? 'hidden' : '' ?>>Sem foto de capa</span>
                    </div>
                    <form method="post" action="<?= site_url('perfil-loja/imagem/capa') ?>" enctype="multipart/form-data" class="el-form-imagem">
                        <?= csrf_field() ?>
                        <label for="el-imagem-capa">Escolher foto de fundo</label>
                        <input id="el-imagem-capa" name="imagem" type="file" accept="image/jpeg,image/png,image/webp" required data-previa="el-previa-img-capa" data-vazio="el-sem-foto-capa">
                        <button class="el-btn el-salvar" type="submit">Salvar foto de fundo</button>
                    </form>
                </section>
            </div>
            <p class="el-ajuda">Imagens aceitas: JPG, PNG ou WebP, até 5 MB cada. As fotos são salvas separadamente dos dados cadastrais.</p>
            <h2 class="el-subtitulo">Dados cadastrais</h2>
            <form id="formEditarLoja" method="post" action="<?= site_url('perfil-loja/atualizar') ?>" data-url-cidades="<?= site_url('cidades') ?>">
                <?= csrf_field() ?>
                <div class="el-grid">
                    <div class="el-campo el-largo">
                        <label for="nome_empresa">Nome da empresa</label>
                        <input id="nome_empresa" name="nome_empresa" type="text" maxlength="150" required value="<?= esc(old('nome_empresa', $loja['nome_empresa'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="el-campo el-largo" id="el-campo-cidade">
                        <label for="cidade_busca">Cidade da loja</label>
                        <input id="cidade_busca" type="text" autocomplete="off" required value="<?= esc(old('cidade_busca', $nomeCidade), 'attr') ?>" placeholder="Digite o nome da cidade" aria-controls="el-sugestoes">
                        <input id="cidade_id" name="cidade_id" type="hidden" value="<?= esc(old('cidade_id', $loja['cidade_id'] ?? ''), 'attr') ?>">
                        <div id="el-sugestoes" class="el-sugestoes" role="listbox" hidden></div>
                        <span class="el-ajuda">Selecione uma cidade da lista para confirmar.</span>
                    </div>
                    <div class="el-campo el-largo">
                        <label for="contato">Contato da loja</label>
                        <input id="contato" name="contato" type="text" maxlength="30" required value="<?= esc(old('contato', $loja['contato'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="el-campo el-largo">
                        <label for="cnpj">CNPJ (somente consulta)</label>
                        <?php $cnpj = (string) ($loja['cnpj'] ?? ''); ?>
                        <input id="cnpj" type="text" readonly value="<?= esc($cnpj !== '' ? $cnpj : 'Não informado', 'attr') ?>">
                    </div>
                </div>
                <div class="el-acoes">
                    <a href="<?= site_url('perfil-loja') ?>" class="el-btn el-cancelar">Cancelar</a>
                    <button id="el-salvar" type="submit" class="el-btn el-salvar">Salvar alterações</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
