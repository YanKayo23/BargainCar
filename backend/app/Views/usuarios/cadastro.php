
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua conta - BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/cadastro.css') ?>">
</head>
<body>

<main class="cadastro-container">

    <aside class="painel-azul">
        <div class="marca">
            <span class="marca-icone" aria-hidden="true"></span>
            <span>BargainCar</span>
        </div>

        <h1>Compre e venda<br>carros perto de<br>você</h1>

        <ul class="beneficios">
            <li>Anúncios da sua região</li>
            <li>Negocie direto com o vendedor</li>
            <li>Seus dados ficam protegidos</li>
        </ul>

        <div class="painel-login">
            <p>Já tem conta?</p>
            <a href="<?= site_url('login') ?>" class="botao-entrar">
                Entrar agora
            </a>
        </div>
    </aside>

    <section class="painel-formulario">
        <h2>Crie sua conta</h2>

        <?php if (session()->getFlashdata('sucesso')): ?>
            <div class="sucesso" role="status">
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
                <input
                    id="nome"
                    name="nome"
                    type="text"
                    maxlength="100"
                    autocomplete="name"
                    value="<?= esc(old('nome')) ?>"
                    required
                >
            </div>

            <div class="linha-campos">
                <div class="campo">
                    <label for="email">E-mail</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        maxlength="150"
                        autocomplete="email"
                        value="<?= esc(old('email')) ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="contato_padrao">Contato (WhatsApp)</label>
                    <input
                        id="contato_padrao"
                        name="contato_padrao"
                        type="tel"
                        maxlength="30"
                        inputmode="tel"
                        placeholder="(00) 00000-0000"
                        value="<?= esc(old('contato_padrao')) ?>"
                    >
                </div>
            </div>

            <div class="linha-campos">
                <div class="campo">
                    <label for="cpf">CPF</label>
                    <input
                        id="cpf"
                        name="cpf"
                        type="text"
                        maxlength="14"
                        inputmode="numeric"
                        placeholder="000.000.000-00"
                        autocomplete="off"
                        value="<?= esc(old('cpf')) ?>"
                        required
                    >
                </div>

                <div class="campo campo-cidade">
                    <label for="cidade_busca">Sua cidade</label>

                    <div class="cidade-container">
                        <input
                            id="cidade_busca"
                            type="text"
                            placeholder="Escolha na lista"
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="resultados_cidades"
                            required
                        >

                        <input
                            id="cidade_id"
                            name="cidade_id"
                            type="hidden"
                            value="<?= esc(old('cidade_id')) ?>"
                        >

                        <div
                            id="resultados_cidades"
                            role="listbox"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>

                <div class="senha-container">
                    <input
                        id="senha"
                        name="senha"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        id="alternar-senha"
                        class="botao-olho"
                        aria-label="Mostrar senha"
                        aria-pressed="false"
                    >
                        <svg
                            id="icone-olho"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="botao-cadastrar">
                Cadastrar
            </button>
        </form>
    </section>
</main>

<script src="<?= base_url('js/cadastro.js') ?>"></script>
</body>
</html>
