
document.addEventListener('DOMContentLoaded', () => {
    // =========================================
    // 1. NAVBAR FIXA NO TOPO
    // =========================================

    const navbar = document.getElementById('bc-navbar');
    const espacador = document.getElementById('bc-navbar-espacador');

    if (navbar && espacador) {
        const ajustar = () => {
            espacador.style.height =
                `${navbar.getBoundingClientRect().height}px`;
        };

        ajustar();
        window.addEventListener('resize', ajustar);

        if ('ResizeObserver' in window) {
            new ResizeObserver(ajustar).observe(navbar);
        }
    }

    // =========================================
    // 2. MENU PESSOA / LOJA
    // =========================================

    const menuBotao = document.getElementById('bc-menu-botao');
    const menuPerfis = document.getElementById('bc-menu-perfis');

    const fecharPerfis = () => {
        if (!menuBotao || !menuPerfis) return;

        menuPerfis.hidden = true;
        menuBotao.setAttribute('aria-expanded', 'false');
    };

    menuBotao?.addEventListener('click', () => {
        if (!menuPerfis) return;

        const abrir = menuPerfis.hidden;
        menuPerfis.hidden = !abrir;

        menuBotao.setAttribute('aria-expanded', String(abrir));

        if (abrir) fecharDistancia();
    });

    // =========================================
    // 3. ELEMENTOS DA PESQUISA
    // =========================================

    const form = document.querySelector('.bc-navbar-pesquisa');

    const cidade = document.getElementById('bc-cidade-busca');
    const cidadeId = document.getElementById('bc-cidade-id');
    const sugestoes = document.getElementById('bc-cidades-sugestoes');

    const distanciaContainer =
        document.getElementById('bc-distancia-container');

    const distanciaBotao =
        document.getElementById('bc-distancia-botao');

    const distanciaOpcoes =
        document.getElementById('bc-distancia-opcoes');

    const distanciaInput =
        document.getElementById('bc-distancia');

    const distanciaTexto =
        document.getElementById('bc-distancia-texto');

    const valores = ['10', '25', '50', '75', '100'];

    // =========================================
    // 4. DROPDOWN DE DISTÂNCIA
    // =========================================

    function fecharDistancia(foco = false) {
        if (!distanciaOpcoes || !distanciaBotao) return;

        distanciaOpcoes.hidden = true;
        distanciaBotao.setAttribute('aria-expanded', 'false');

        if (foco) distanciaBotao.focus();
    }

    function selecionarDistancia(valor) {
        if (
            !valores.includes(valor) ||
            !distanciaInput ||
            !distanciaTexto ||
            !distanciaOpcoes
        ) return;

        distanciaInput.value = valor;
        distanciaTexto.textContent = `${valor} km`;

        distanciaOpcoes
            .querySelectorAll('.bc-distancia-opcao')
            .forEach(opcao => {
                const ativa = opcao.dataset.km === valor;

                opcao.classList.toggle('is-selected', ativa);
                opcao.setAttribute('aria-selected', String(ativa));
            });

        fecharDistancia(true);
    }

    function abrirDistancia() {
        if (!distanciaOpcoes || !distanciaBotao) return;

        distanciaOpcoes.hidden = false;
        distanciaBotao.setAttribute('aria-expanded', 'true');

        fecharPerfis();

        const atual = distanciaOpcoes.querySelector(
            '.bc-distancia-opcao.is-selected'
        );

        (
            atual ||
            distanciaOpcoes.querySelector('.bc-distancia-opcao')
        )?.focus();
    }

    if (
        distanciaBotao &&
        distanciaOpcoes &&
        distanciaInput &&
        distanciaTexto
    ) {
        if (!valores.includes(distanciaInput.value)) {
            distanciaInput.value = '100';
        }

        distanciaTexto.textContent =
            `${distanciaInput.value} km`;

        distanciaBotao.addEventListener('click', () => {
            distanciaOpcoes.hidden
                ? abrirDistancia()
                : fecharDistancia();
        });

        distanciaBotao.addEventListener('keydown', evento => {
            if (
                evento.key === 'ArrowDown' ||
                evento.key === 'ArrowUp'
            ) {
                evento.preventDefault();
                abrirDistancia();
            }
        });

        const opcoes = [
            ...distanciaOpcoes.querySelectorAll(
                '.bc-distancia-opcao'
            )
        ];

        opcoes.forEach((opcao, indice) => {
            opcao.addEventListener('click', () => {
                selecionarDistancia(opcao.dataset.km);
            });

            opcao.addEventListener('keydown', evento => {
                if (
                    evento.key === 'ArrowDown' ||
                    evento.key === 'ArrowUp'
                ) {
                    evento.preventDefault();

                    const proximo = (
                        indice +
                        (evento.key === 'ArrowDown' ? 1 : -1) +
                        opcoes.length
                    ) % opcoes.length;

                    opcoes[proximo].focus();

                } else if (
                    evento.key === 'Home' ||
                    evento.key === 'End'
                ) {
                    evento.preventDefault();

                    opcoes[
                        evento.key === 'Home'
                            ? 0
                            : opcoes.length - 1
                    ].focus();

                } else if (evento.key === 'Escape') {
                    evento.preventDefault();
                    fecharDistancia(true);

                } else if (evento.key === 'Tab') {
                    fecharDistancia();
                }
            });
        });
    }

    // =========================================
    // 5. AUTOCOMPLETE DE CIDADES
    // =========================================

    if (!form || !cidade || !cidadeId || !sugestoes) return;

    let temporizador;
    let buscaAtual;

    const limparSugestoes = () => {
        sugestoes.replaceChildren();
        sugestoes.hidden = true;
        cidade.setAttribute('aria-expanded', 'false');
    };

    const aviso = mensagem => {
        limparSugestoes();

        const item = document.createElement('div');
        item.className = 'bc-navbar-sugestao-vazia';
        item.textContent = mensagem;

        sugestoes.appendChild(item);
        sugestoes.hidden = false;

        cidade.setAttribute('aria-expanded', 'true');
    };

    cidade.addEventListener('input', () => {
        cidadeId.value = '';

        clearTimeout(temporizador);
        buscaAtual?.abort();
        limparSugestoes();

        const termo = cidade.value.trim();

        if (termo.length < 2) return;

        temporizador = setTimeout(async () => {
            const controlador = new AbortController();
            buscaAtual = controlador;

            try {
                const url = new URL(
                    form.dataset.cidadesUrl,
                    window.location.href
                );

                url.searchParams.set('busca', termo);

                const resposta = await fetch(url.toString(), {
                    signal: controlador.signal,
                    headers: {
                        Accept: 'application/json'
                    }
                });

                if (!resposta.ok) {
                    throw new Error('Erro ao consultar cidades');
                }

                const cidades = await resposta.json();

                if (
                    controlador.signal.aborted ||
                    cidade.value.trim() !== termo
                ) return;

                limparSugestoes();

                if (
                    !Array.isArray(cidades) ||
                    cidades.length === 0
                ) {
                    aviso('Nenhuma cidade encontrada');
                    return;
                }

                cidades.forEach(item => {
                    if (
                        !item.codigo_ibge ||
                        !item.nome ||
                        !item.uf
                    ) return;

                    const botao = document.createElement('button');

                    botao.type = 'button';
                    botao.className = 'bc-navbar-sugestao';
                    botao.setAttribute('role', 'option');

                    botao.textContent =
                        `${item.nome} - ${item.uf}`;

                    botao.addEventListener('click', () => {
                        cidade.value = botao.textContent;
                        cidadeId.value = String(item.codigo_ibge);

                        limparSugestoes();
                    });

                    sugestoes.appendChild(botao);
                });

                if (!sugestoes.children.length) {
                    aviso('Nenhuma cidade encontrada');
                } else {
                    sugestoes.hidden = false;
                    cidade.setAttribute('aria-expanded', 'true');
                }

            } catch (erro) {
                if (erro.name !== 'AbortError') {
                    console.error('Erro ao buscar cidades:', erro);
                }
            }

        }, 300);
    });

    // =========================================
    // 6. VALIDAÇÃO DO FORMULÁRIO
    // =========================================

    form.addEventListener('submit', evento => {
        const digitada = cidade.value.trim();

        if (digitada && !cidadeId.value) {
            evento.preventDefault();
            cidade.focus();

            aviso(
                'Selecione uma cidade da lista de sugestões.'
            );

            return;
        }

        if (
            !digitada &&
            !cidadeId.value &&
            form.dataset.logado !== '1'
        ) {
            evento.preventDefault();
            cidade.focus();

            aviso(
                'Selecione uma cidade para realizar a busca.'
            );

            return;
        }

        // Garante que apenas os cinco raios
        // predefinidos sejam enviados.
        if (
            distanciaInput &&
            !valores.includes(distanciaInput.value)
        ) {
            distanciaInput.value = '100';
        }
    });

    // =========================================
    // 7. FECHAR MENUS
    // =========================================

    document.addEventListener('click', evento => {
        if (
            menuBotao &&
            menuPerfis &&
            !menuBotao.contains(evento.target) &&
            !menuPerfis.contains(evento.target)
        ) {
            fecharPerfis();
        }

        if (
            !cidade.contains(evento.target) &&
            !sugestoes.contains(evento.target)
        ) {
            limparSugestoes();
        }

        if (
            distanciaContainer &&
            !distanciaContainer.contains(evento.target)
        ) {
            fecharDistancia();
        }
    });

    document.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') {
            fecharPerfis();
            limparSugestoes();
            fecharDistancia();
        }
    });
});
