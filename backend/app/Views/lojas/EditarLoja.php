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
            <form id="formEditarLoja" method="post" enctype="multipart/form-data" action="<?= site_url('perfil-loja/atualizar') ?>" data-url-cidades="<?= site_url('cidades') ?>">
                <?= csrf_field() ?>
            <div class="el-fotos">
                <section class="el-foto-card" aria-labelledby="el-titulo-perfil">
                    <h2 id="el-titulo-perfil">Foto de perfil da loja</h2>
                    <div class="el-previa el-previa-perfil">
                        <img id="el-previa-img-perfil" src="<?= !empty($loja['foto_perfil']) ? esc(base_url($loja['foto_perfil']), 'attr') : '' ?>" alt="Prévia da foto de perfil" <?= empty($loja['foto_perfil']) ? 'hidden' : '' ?>>
                        <span id="el-sem-foto-perfil" <?= !empty($loja['foto_perfil']) ? 'hidden' : '' ?>>Sem foto</span>
                    </div>
                    <div class="el-form-imagem">
                        <label for="el-imagem-perfil">Escolher foto de perfil</label>
                        <input id="el-imagem-perfil" name="foto_perfil" type="file" accept="image/jpeg,image/png,image/webp" data-previa="el-previa-img-perfil" data-vazio="el-sem-foto-perfil">
                        <input type="hidden" name="remover_foto_perfil" id="el-remover-perfil" value="0">
                        <button type="button" class="el-remover" data-remover="perfil" <?= empty($loja['foto_perfil']) ? 'disabled' : '' ?>>Remover foto</button>
                        <button type="button" class="el-desfazer" data-desfazer="perfil" hidden>Desfazer remoção</button>
                    </div>
                </section>
                <section class="el-foto-card" aria-labelledby="el-titulo-capa">
                    <h2 id="el-titulo-capa">Foto de fundo (capa)</h2>
                    <div class="el-previa el-previa-capa">
                        <img id="el-previa-img-capa" src="<?= !empty($loja['foto_capa']) ? esc(base_url($loja['foto_capa']), 'attr') : '' ?>" alt="Prévia da foto de capa" <?= empty($loja['foto_capa']) ? 'hidden' : '' ?>>
                        <span id="el-sem-foto-capa" <?= !empty($loja['foto_capa']) ? 'hidden' : '' ?>>Sem foto de capa</span>
                    </div>
                    <div class="el-form-imagem">
                        <label for="el-imagem-capa">Escolher foto de fundo</label>
                        <input id="el-imagem-capa" name="foto_capa" type="file" accept="image/jpeg,image/png,image/webp" data-previa="el-previa-img-capa" data-vazio="el-sem-foto-capa">
                        <input type="hidden" name="remover_foto_capa" id="el-remover-capa" value="0">
                        <button type="button" class="el-remover" data-remover="capa" <?= empty($loja['foto_capa']) ? 'disabled' : '' ?>>Remover foto</button>
                        <button type="button" class="el-desfazer" data-desfazer="capa" hidden>Desfazer remoção</button>
                    </div>
                </section>
            </div>
            <p class="el-ajuda">Imagens aceitas: JPG, PNG ou WebP, até 5 MB cada. As fotos e os dados serão gravados somente ao clicar em Salvar alterações.</p>
            <h2 class="el-subtitulo">Dados cadastrais</h2>
                <div class="el-grid">
                    <div class="el-campo el-largo">
                        <label for="nome_empresa">Nome da empresa</label>
                        <input id="nome_empresa" name="nome_empresa" type="text" maxlength="150" required value="<?= esc(old('nome_empresa', $loja['nome_empresa'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="el-campo el-largo" id="el-campo-cidade">
                        <label for="cidade_busca">Cidade da loja</label>
                        <input id="cidade_busca" name="cidade_busca" type="text" autocomplete="off" required value="<?= esc(old('cidade_busca', $nomeCidade), 'attr') ?>" placeholder="Digite o nome da cidade" aria-controls="el-sugestoes">
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
