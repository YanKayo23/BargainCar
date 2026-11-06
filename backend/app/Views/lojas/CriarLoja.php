
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar minha loja - BargainCar</title>

    <link rel="stylesheet" href="<?= base_url('css/CriarLoja.css') ?>">
</head>

<body>

    <header class="cabecalho">
        <div class="cabecalho-conteudo">

            <a href="<?= site_url('/') ?>" class="marca">
                <span class="marca-icone" aria-hidden="true"></span>
                <span>BargainCar</span>
            </a>

            <nav class="navegacao" aria-label="Navegação principal">
                <a href="<?= site_url('/') ?>">Início</a>

                <span class="perfil-atual">
                    <span class="perfil-indicador"></span>
                    Pessoa
                </span>
            </nav>

        </div>
    </header>

    <main class="pagina">

        <a href="<?= site_url('/') ?>" class="voltar">
            <span aria-hidden="true">←</span>
            Voltar
        </a>

        <div class="titulo-pagina">
            <h1>Criar minha loja</h1>

            <p>
                Com a loja, você anuncia em nome da sua empresa.
                Seus dados pessoais continuam separados.
            </p>
        </div>

        <?php if (session()->getFlashdata('erro')): ?>
            <div class="mensagem mensagem-erro" role="alert">
                <?= esc(session()->getFlashdata('erro')) ?>
            </div>
        <?php endif; ?>

        <?php
            $erros = session()->getFlashdata('erros') ?? [];
        ?>

        <?php if (! empty($erros)): ?>
            <div class="mensagem mensagem-erro" role="alert">
                <strong>Verifique os seguintes campos:</strong>

                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= esc($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="conteudo-principal">

            <!-- FORMULÁRIO -->
            <section class="cartao formulario-cartao">

                <form
                    id="form-loja"
                    action="<?= site_url('criar-loja') ?>"
                    method="post">

                    <?= csrf_field() ?>

                    <div class="campo">
                        <label for="nome_empresa">
                            Nome da empresa
                        </label>

                        <input
                            type="text"
                            id="nome_empresa"
                            name="nome_empresa"
                            maxlength="150"
                            placeholder="Digite o nome da sua loja"
                            value="<?= esc(old('nome_empresa')) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label for="cnpj">
                            CNPJ
                            <span class="opcional">(opcional)</span>
                        </label>

                        <input
                            type="text"
                            id="cnpj"
                            name="cnpj"
                            maxlength="18"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="00.000.000/0000-00"
                            value="<?= esc(old('cnpj')) ?>">
                    </div>

                    <div class="linha-campos">

                        <!-- CIDADE -->
                        <div class="campo campo-cidade">

                            <label for="cidade_busca">
                                Cidade da loja
                            </label>

                            <div class="cidade-container">

                                <input
                                    type="text"
                                    id="cidade_busca"
                                    name="cidade_busca"
                                    placeholder="Escolha na lista"
                                    autocomplete="off"
                                    role="combobox"
                                    aria-autocomplete="list"
                                    aria-expanded="false"
                                    aria-controls="resultados_cidades"
                                    value="<?= esc(old('cidade_busca')) ?>"
                                    required>

                                <input
                                    type="hidden"
                                    id="cidade_id"
                                    name="cidade_id"
                                    value="<?= esc(old('cidade_id')) ?>">

                                <div
                                    id="resultados_cidades"
                                    role="listbox">
                                </div>

                            </div>
                        </div>

                        <!-- CONTATO -->
                        <div class="campo">

                            <label for="contato">
                                Contato da loja
                            </label>

                            <input
                                type="tel"
                                id="contato"
                                name="contato"
                                maxlength="30"
                                inputmode="tel"
                                placeholder="(00) 00000-0000"
                                value="<?= esc(old('contato')) ?>"
                                required>

                        </div>

                    </div>

                    <div class="acoes">

                        <a
                            href="<?= site_url('/') ?>"
                            class="botao botao-cancelar">
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="botao botao-salvar">
                            Salvar loja
                        </button>

                    </div>

                </form>

            </section>

            <!-- PRÉ-VISUALIZAÇÃO -->
            <aside class="cartao previa-cartao">

                <h2>Como sua loja aparece</h2>

                <div class="previa-capa">
                    <span class="capa-icone" aria-hidden="true">▧</span>
                    <span>Foto de capa (depois)</span>
                </div>

                <div class="previa-informacoes">

                    <div class="previa-avatar" aria-hidden="true">
                        <span id="previa-inicial">L</span>
                    </div>

                    <div class="previa-dados">
                        <h3 id="previa-nome">
                            Nome da empresa
                        </h3>

                        <p id="previa-cidade">
                            Cidade da loja
                        </p>
                    </div>

                </div>

                <p class="previa-observacao">
                    Você poderá colocar a foto de capa
                    depois de criar a loja.
                </p>

            </aside>

        </div>

        <div class="aviso">

            <span class="aviso-icone" aria-hidden="true">ⓘ</span>

            <p>
                Você pode ter apenas uma loja ativa por vez.
                O CNPJ e o contato da loja não serão exibidos
                publicamente. Se excluir uma loja, poderá
                criar outra ou reativar a anterior, desde que
                não possua outra loja ativa.
            </p>

        </div>

    </main>

    <script src="<?= base_url('js/CriarLoja.js') ?>"></script>

</body>
</html>
