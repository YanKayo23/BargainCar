<?php
/** @var array $usuario */
/** @var string $nomeCidade */
$usuario = $usuario ?? [];
$nomeCidade = $nomeCidade ?? '';
$erros = session()->getFlashdata('erros') ?? [];
$cpf = (string) old('cpf', $usuario['cpf'] ?? '');
$cpf = preg_replace('/\D/', '', $cpf);
if (strlen($cpf) === 11) {
    $cpf = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}
$nomeCidadeExibido = old('cidade_busca', $nomeCidade);
$codigoCidade = old('cidade_id', $usuario['cidade_id'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar meus dados | BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/EditarPerfil.css') ?>">
    <script src="<?= base_url('js/EditarPerfil.js') ?>" defer></script>
</head>
<body>
    <?= view('layouts/Navbar') ?>

    <main class="editar-perfil-container">
        <a class="editar-perfil-voltar" href="<?= site_url('meu-perfil') ?>">← Voltar ao meu perfil</a>
        <header class="editar-perfil-cabecalho">
            <h1>Editar meus dados</h1>
            <p>Atualize suas informações pessoais. Os campos com * são obrigatórios.</p>
        </header>

        <?php if ($erros): ?>
            <div class="editar-perfil-erros" role="alert">
                <strong>Confira as informações:</strong>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= esc($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="formEditarPerfil" action="<?= site_url('editar-perfil') ?>"
              method="post" enctype="multipart/form-data" data-url-cidades="<?= esc(site_url('cidades'), 'attr') ?>">
            <?= csrf_field() ?>
            <?php $fotoAtual = $usuario['foto_perfil'] ?? ''; ?>
            <section class="editar-perfil-card">
                <h2>Foto de perfil</h2>
                <div class="ep-foto-area">
                    <div class="ep-avatar">
                        <img id="fotoPrevia" alt="Foto de perfil" <?php if ($fotoAtual): ?>src="<?= esc(base_url($fotoAtual), 'attr') ?>"<?php else: ?>hidden<?php endif; ?>>
                        <span id="fotoInicial" <?= $fotoAtual ? 'hidden' : '' ?>><?= esc(mb_strtoupper(mb_substr($usuario['nome'] ?? 'U', 0, 1))) ?></span>
                    </div>
                    <div class="editar-perfil-campo">
                        <label for="foto_perfil">Escolher nova foto</label>
                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/webp">
                        <input type="hidden" id="remover_foto" name="remover_foto" value="0">
                        <div class="ep-foto-acoes">
                            <button type="button" id="removerFoto" <?= $fotoAtual ? '' : 'hidden' ?>>Remover foto</button>
                            <button type="button" id="desfazerFoto" hidden>Desfazer remoção</button>
                        </div>
                        <small>JPG, PNG ou WebP, até 2 MB. A foto só será alterada ao salvar.</small>
                    </div>
                </div>
            </section>
            <section class="editar-perfil-card">
                <h2>Informações pessoais</h2>
                <div class="editar-perfil-grid">
                    <div class="editar-perfil-campo editar-perfil-largo">
                        <label for="nome">Nome completo *</label>
                        <input id="nome" name="nome" type="text" maxlength="100" required
                               autocomplete="name" value="<?= esc(old('nome', $usuario['nome'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="editar-perfil-campo">
                        <label for="cpf">CPF *</label>
                        <input id="cpf" name="cpf" type="text" inputmode="numeric"
                               maxlength="14" required autocomplete="off"
                               value="<?= esc($cpf, 'attr') ?>">
                    </div>
                    <div class="editar-perfil-campo">
                        <label for="email">E-mail *</label>
                        <input id="email" name="email" type="email" maxlength="150" required
                               autocomplete="email" value="<?= esc(old('email', $usuario['email'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="editar-perfil-campo">
                        <label for="contato_padrao">Contato padrão (opcional)</label>
                        <input id="contato_padrao" name="contato_padrao" type="text" maxlength="30"
                               value="<?= esc(old('contato_padrao', $usuario['contato_padrao'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="editar-perfil-campo campo-cidade">
                        <label for="cidade_busca">Cidade padrão *</label>
                        <input id="cidade_busca" name="cidade_busca" type="text"
                               maxlength="100" autocomplete="off" required
                               aria-autocomplete="list" aria-controls="sugestoesCidades"
                               value="<?= esc($nomeCidadeExibido, 'attr') ?>"
                               placeholder="Digite o nome da cidade">
                        <input id="cidade_id" name="cidade_id" type="hidden"
                               value="<?= esc($codigoCidade, 'attr') ?>">
                        <div id="sugestoesCidades" class="editar-perfil-sugestoes"
                             role="listbox" hidden></div>
                        <small>Selecione uma cidade da lista de sugestões.</small>
                    </div>
                </div>
            </section>

            <section class="editar-perfil-card">
                <h2>Confirmação de segurança</h2>
                <p class="editar-perfil-explicacao">Se você alterar o CPF ou o e-mail, informe sua senha atual para confirmar a mudança. Não é necessário digitar a senha para alterar apenas nome, contato, cidade ou foto de perfil.</p>
                <div class="editar-perfil-campo editar-perfil-senha">
                    <label for="senha_atual">Senha atual</label>
                    <div class="editar-perfil-senha-linha">
                        <input id="senha_atual" name="senha_atual" type="password"
                               autocomplete="current-password" placeholder="Informe sua senha atual">
                        <button id="mostrarSenha" type="button" aria-label="Mostrar senha">Mostrar</button>
                    </div>
                </div>
            </section>
            <div class="editar-perfil-acoes">
                <a href="<?= site_url('meu-perfil') ?>" class="editar-perfil-cancelar">Cancelar</a>
                <button type="submit" id="botaoSalvar">Salvar alterações</button>
            </div>
        </form>
    </main>
</body>
</html>
