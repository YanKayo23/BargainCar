
document.addEventListener('DOMContentLoaded', () => {

    // =========================================
    // 1. MENU PESSOA / LOJA
    // =========================================

    const botaoMenu = document.getElementById('bc-menu-botao');
    const menuPerfis = document.getElementById('bc-menu-perfis');

    if (botaoMenu && menuPerfis) {

        botaoMenu.addEventListener('click', () => {
            const estaAberto = !menuPerfis.hidden;

            menuPerfis.hidden = estaAberto;

            botaoMenu.setAttribute(
                'aria-expanded',
                String(!estaAberto)
            );
        });

        // Fecha o menu ao clicar fora dele.
        document.addEventListener('click', (evento) => {
            if (
                !botaoMenu.contains(evento.target) &&
                !menuPerfis.contains(evento.target)
            ) {
                fecharMenu();
            }
        });

        // Fecha o menu ao pressionar ESC.
        document.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape') {
                fecharMenu();
            }
        });

        function fecharMenu() {
            menuPerfis.hidden = true;
            botaoMenu.setAttribute('aria-expanded', 'false');
        }
    }


    // =========================================
    // 2. AUTOCOMPLETE DE CIDADES
    // =========================================

    const campoCidade = document.getElementById('bc-cidade-busca');
    const campoCidadeId = document.getElementById('bc-cidade-id');
    const listaCidades = document.getElementById('bc-cidades-sugestoes');

    if (!campoCidade || !campoCidadeId || !listaCidades) {
        return;
    }

    let temporizador = null;
    let controladorBusca = null;

    function limparSugestoes() {
        listaCidades.replaceChildren();
        listaCidades.hidden = true;
    }

    campoCidade.addEventListener('input', () => {

        // A cidade anterior deixa de ser válida
        // quando o usuário altera o texto.
        campoCidadeId.value = '';

        clearTimeout(temporizador);

        if (controladorBusca) {
            controladorBusca.abort();
        }

        limparSugestoes();

        const termo = campoCidade.value.trim();

        if (termo.length < 2) {
            return;
        }

        // Aguarda 300 ms antes de consultar o servidor.
        temporizador = setTimeout(async () => {

            controladorBusca = new AbortController();
            const buscaAtual = controladorBusca;

            try {
                const resposta = await fetch(
                    '/cidades?busca=' + encodeURIComponent(termo),
                    {
                        signal: buscaAtual.signal,
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                if (!resposta.ok) {
                    throw new Error('Erro ao buscar cidades.');
                }

                const cidades = await resposta.json();

                // Ignora resultados de pesquisas antigas.
                if (
                    buscaAtual.signal.aborted ||
                    campoCidade.value.trim() !== termo
                ) {
                    return;
                }

                limparSugestoes();

                if (!Array.isArray(cidades)) {
                    return;
                }

                cidades.forEach((cidade) => {

                    const codigo = cidade.codigo_ibge;
                    const nome = cidade.nome;
                    const uf = cidade.uf;

                    if (!codigo || !nome || !uf) {
                        return;
                    }

                    const botao = document.createElement('button');

                    botao.type = 'button';
                    botao.className = 'bc-navbar-sugestao';

                    botao.textContent = `${nome} - ${uf}`;

                    botao.addEventListener('click', () => {
                        campoCidade.value = `${nome} - ${uf}`;
                        campoCidadeId.value = codigo;

                        limparSugestoes();
                    });

                    listaCidades.appendChild(botao);
                });

                listaCidades.hidden =
                    listaCidades.children.length === 0;

            } catch (erro) {
                if (erro.name !== 'AbortError') {
                    console.error(
                        'Não foi possível carregar as cidades.',
                        erro
                    );
                }
            }

        }, 300);
    });


    // =========================================
    // 3. VALIDAÇÃO DA CIDADE
    // =========================================

    const formularioPesquisa = campoCidade.closest('form');

    if (formularioPesquisa) {

        formularioPesquisa.addEventListener('submit', (evento) => {

            const cidadeDigitada = campoCidade.value.trim();
            const cidadeSelecionada = campoCidadeId.value;

            // Sem cidade digitada: o backend utilizará
            // a cidade padrão do usuário.
            if (cidadeDigitada === '') {
                campoCidadeId.value = '';
                return;
            }

            // Impede enviar uma cidade que não foi
            // selecionada na lista de sugestões.
            if (!cidadeSelecionada) {
                evento.preventDefault();

                campoCidade.focus();

                alert(
                    'Selecione uma cidade na lista de sugestões ou deixe o campo vazio.'
                );
            }
        });
    }


    // Fecha sugestões ao clicar fora.
    document.addEventListener('click', (evento) => {

        if (
            !campoCidade.contains(evento.target) &&
            !listaCidades.contains(evento.target)
        ) {
            limparSugestoes();
        }
    });

});
