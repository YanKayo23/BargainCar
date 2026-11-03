
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Entrar - BargainCar</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f4f5fa;
            font-family: Arial, sans-serif;
            color: #171717;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 865px;
            min-height: 430px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,.08);
        }

        .painel-esquerdo {
            width: 37%;
            padding: 40px 32px;
            background: #0787f8;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
            font-weight: bold;
        }

        .logo-icone {
            width: 32px;
            height: 40px;
            background: white;
            border-radius: 8px;
        }

        .painel-esquerdo h1 {
            font-size: 29px;
            margin-top: 35px;
            margin-bottom: 28px;
        }

        .painel-esquerdo p {
            font-size: 17px;
            line-height: 1.5;
            margin: 0;
        }

        .botao-cadastro {
            margin-top: auto;
            display: block;
            padding: 17px;
            text-align: center;
            background: white;
            color: #005da9;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
        }

        .painel-direito {
            width: 63%;
            padding: 45px;
        }

        .painel-direito h2 {
            margin: 0 0 25px;
            font-size: 27px;
        }

        .campo {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            height: 52px;
            padding: 12px;
            border: 1px solid #aab4bf;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #0787f8;
            box-shadow: 0 0 0 2px rgba(7,135,248,.12);
        }

        .senha-container {
            position: relative;
        }

        .senha-container input {
            padding-right: 55px;
        }

        .mostrar-senha {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #555;
            padding: 6px;
        }

        .mostrar-senha svg {
            width: 21px;
            height: 21px;
            display: block;
        }

        .esqueci-senha {
            display: inline-block;
            margin-bottom: 18px;
            color: #0064b7;
            font-weight: bold;
            font-size: 14px;
        }

        .botao-entrar {
            width: 100%;
            height: 55px;
            border: none;
            border-radius: 8px;
            background: #0064c7;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .botao-entrar:hover {
            background: #004f9f;
        }

        .erro {
            background: #ffe4e4;
            color: #a00000;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        @media (max-width: 650px) {
            .container {
                flex-direction: column;
            }

            .painel-esquerdo,
            .painel-direito {
                width: 100%;
                padding: 28px;
            }

            .painel-esquerdo {
                gap: 15px;
            }

            .painel-esquerdo h1 {
                margin: 10px 0 0;
            }

            .botao-cadastro {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <aside class="painel-esquerdo">

        <div class="logo">
            <div class="logo-icone"></div>
            BargainCar
        </div>

        <h1>Novo por aqui?</h1>

        <p>Crie sua conta em poucos minutos.</p>

        <a href="<?= site_url('cadastro') ?>"
           class="botao-cadastro">
            Criar conta
        </a>

    </aside>

    <main class="painel-direito">

        <h2>Entrar na sua conta</h2>

        <?php if (session()->getFlashdata('erro')): ?>
            <div class="erro" role="alert">
                <?= esc(session()->getFlashdata('erro')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>"
              method="post">

            <?= csrf_field() ?>

            <div class="campo">
                <label for="cpf">CPF</label>

                <input
                    type="text"
                    id="cpf"
                    name="cpf"
                    placeholder="000.000.000-00"
                    maxlength="14"
                    inputmode="numeric"
                    autocomplete="username"
                    value="<?= esc(old('cpf')) ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="senha">Senha</label>

                <div class="senha-container">
                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="mostrar-senha"
                        id="alternarSenha"
                        aria-label="Mostrar senha"
                        aria-pressed="false"
                    >
                        <svg viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <a href="<?= site_url('esqueci-senha') ?>"
               class="esqueci-senha">
                Esqueci minha senha
            </a>

            <button type="submit" class="botao-entrar">
                Entrar
            </button>

        </form>

    </main>

</div>

<script>
    const cpf = document.getElementById('cpf');

    cpf.addEventListener('input', function () {
        let valor = this.value.replace(/\D/g, '');
        valor = valor.substring(0, 11);

        valor = valor.replace(/^(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
        valor = valor.replace(/\.(\d{3})(\d)/, '.$1-$2');

        this.value = valor;
    });

    const senha = document.getElementById('senha');
    const botaoSenha = document.getElementById('alternarSenha');

    botaoSenha.addEventListener('click', function () {
        const visivel = senha.type === 'text';

        senha.type = visivel ? 'password' : 'text';

        botaoSenha.setAttribute(
            'aria-label',
            visivel ? 'Mostrar senha' : 'Ocultar senha'
        );

        botaoSenha.setAttribute(
            'aria-pressed',
            String(!visivel)
        );
    });
</script>

</body>
</html>
