
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar cidades - BargainCar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            font-size: 16px;
        }

        #resultados {
            background: white;
            border: 1px solid #ddd;
        }

        .cidade {
            padding: 12px;
            cursor: pointer;
        }

        .cidade:hover {
            background: #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pesquisar cidade</h2>

        <input
            type="text"
            id="busca"
            placeholder="Digite o nome da cidade"
            autocomplete="off"
        >

        <div id="resultados"></div>
        <p id="selecionada"></p>
    </div>

    <script>
        const input = document.getElementById('busca');
        const resultados = document.getElementById('resultados');
        const selecionada = document.getElementById('selecionada');

        let cidadeSelecionada = null;
        let controlador = null;

        input.addEventListener('input', async () => {
            const termo = input.value.trim();

            cidadeSelecionada = null;
            selecionada.textContent = '';
            resultados.replaceChildren();

            if (controlador) controlador.abort();

            if (termo.length < 2) return;

            controlador = new AbortController();
            const sinal = controlador.signal;

            try {
                const resposta = await fetch(
                    '/cidades?busca=' + encodeURIComponent(termo),
                    { signal: sinal }
                );

                if (!resposta.ok) {
                    throw new Error('Erro na pesquisa');
                }

                const cidades = await resposta.json();

                if (sinal.aborted || input.value.trim() !== termo) return;

                cidades.forEach(cidade => {
                    const item = document.createElement('div');
                    item.className = 'cidade';
                    item.textContent = cidade.nome + ' - ' + cidade.uf;

                    item.addEventListener('click', () => {
                        cidadeSelecionada = cidade.codigo_ibge;
                        input.value = cidade.nome + ' - ' + cidade.uf;
                        selecionada.textContent =
                            'Código IBGE selecionado: ' + cidadeSelecionada;
                        resultados.replaceChildren();
                    });

                    resultados.appendChild(item);
                });
            } catch (erro) {
                if (erro.name !== 'AbortError') {
                    resultados.textContent = 'Não foi possível buscar cidades.';
                }
            }
        });
    </script>
</body>
</html>
