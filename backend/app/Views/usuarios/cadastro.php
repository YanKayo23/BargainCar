
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/cadastro.css') ?>">
</head>
<body>
    <main class="container">
        <h1>Criar conta no BargainCar</h1>
        <p>Preencha os dados para se cadastrar.</p>

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

            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome"
                   maxlength="100" required
                   value="<?= esc(old('nome')) ?>">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email"
                   maxlength="150" required
                   value="<?= esc(old('email')) ?>">

            <label for="cpf">CPF (somente números)</label>
            <input type="text" id="cpf" name="cpf"
                   maxlength="11" inputmode="numeric"
                   pattern="[0-9]{11}" required
                   value="<?= esc(old('cpf')) ?>">

            <label for="cidade_busca">Cidade</label>
            <input type="text" id="cidade_busca"
                   placeholder="Digite sua cidade"
                   autocomplete="off" required>

            <input type="hidden" id="cidade_id"
                   name="cidade_id"
                   value="<?= esc(old('cidade_id')) ?>">

            <div id="resultados_cidades"></div>

            <label for="contato_padrao">Telefone ou WhatsApp</label>
            <input type="text" id="contato_padrao"
                   name="contato_padrao" maxlength="30"
                   value="<?= esc(old('contato_padrao')) ?>">

            <label for="senha">Senha</label>
            <input type="password" id="senha"
                   name="senha" minlength="8"
                   autocomplete="new-password" required>

            <label for="confirmar_senha">Confirmar senha</label>
            <input type="password" id="confirmar_senha"
                   name="confirmar_senha" minlength="8"
                   autocomplete="new-password" required>

            <button type="submit">Criar minha conta</button>
        </form>
    </main>

    <script src="<?= base_url('js/cadastro.js') ?>"></script>
</body>
</html>
