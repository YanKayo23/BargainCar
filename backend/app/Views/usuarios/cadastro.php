
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/cadastro.css') ?>">
</head>
<body>
    <main class="cadastro-container">
        <section class="painel-azul">
            <h1>BargainCar</h1>
            <h2>Bem-vindo de volta</h2>
            <p>Já tenho uma conta</p>
            <a href="<?= site_url('login') ?>" class="botao-entrar">
                Entrar agora
            </a>
        </section>

        <section class="painel-formulario">
            <h2>Crie sua conta</h2>

            <?php if (session()->getFlashdata('sucesso')): ?>
                <div class="sucesso">
                    <?= esc(session()->getFlashdata('sucesso')) ?>
                </div>
            <?php endif; ?>

            <?php $erros = session()->getFlashdata('erros') ?? []; ?>

            <?php if (! empty($erros)): ?>
                <div class="erros" role="alert">
                    <?php foreach ($erros as $erro): ?>
                        <p><?= esc($erro) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('cadastro') ?>" method="post">
                <?= csrf_field() ?>

                <div class="campo">
                    <label for="nome">Nome completo</label>
                    <input id="nome" name="nome" type="text"
                           maxlength="100" required
                           value="<?= esc(old('nome')) ?>">
                </div>

                <div class="linha-campos">
                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email"
                               maxlength="150" required
                               value="<?= esc(old('email')) ?>">
                    </div>

                    <div class="campo">
                        <label for="contato_padrao">Contato (WhatsApp)</label>
                        <input id="contato_padrao" name="contato_padrao"
                               type="tel" maxlength="30"
                               placeholder="(00) 00000-0000"
                               value="<?= esc(old('contato_padrao')) ?>">
                    </div>
                </div>

                <div class="linha-campos">
                    <div class="campo">
                        <label for="cpf">CPF</label>
                        <input id="cpf" name="cpf" type="text"
                               maxlength="11" inputmode="numeric"
                               pattern="[0-9]{11}" required
                               value="<?= esc(old('cpf')) ?>">
                    </div>

                    <div class="campo campo-cidade">
                        <label for="cidade_busca">Sua cidade</label>
                        <input id="cidade_busca" type="text"
                               placeholder="Escolha na lista"
                               autocomplete="off" required
                               role="combobox"
                               aria-autocomplete="list"
                               aria-expanded="false"
                               aria-controls="resultados_cidades">

                        <input id="cidade_id" name="cidade_id"
                               type="hidden"
                               value="<?= esc(old('cidade_id')) ?>">

                        <div id="resultados_cidades"
                             role="listbox"></div>
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <input id="senha" name="senha" type="password"
                           minlength="8" autocomplete="new-password"
                           required>
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <input id="confirmar_senha" name="confirmar_senha"
                           type="password" minlength="8"
                           autocomplete="new-password" required>
                </div>

                <button class="botao-cadastrar" type="submit">
                    Cadastrar
                </button>
            </form>
        </section>
    </main>

    <script src="<?= base_url('js/cadastro.js') ?>"></script>
</body>
</html>
