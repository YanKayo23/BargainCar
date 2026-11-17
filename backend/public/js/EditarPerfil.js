
/* BargainCar — Editar Perfil */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEditarPerfil');

    if (!form) return;

    const cidadeBusca = document.getElementById('cidade_busca');
    const cidadeId = document.getElementById('cidade_id');
    const sugestoes = document.getElementById('sugestoesCidades');
    const cpf = document.getElementById('cpf');
    const senha = document.getElementById('senha_atual');
    const mostrarSenha = document.getElementById('mostrarSenha');
    const botaoSalvar = document.getElementById('botaoSalvar');

    let temporizador = null;
    let requisicao = null;
    let numeroBusca = 0;

    // ==========================================
    // 1. AUTOCOMPLETE DE CIDADES
    // ==========================================

    function fecharSugestoes() {
        sugestoes.replaceChildren();
        sugestoes.hidden = true;
    }

    function cancelarBusca() {
        clearTimeout(temporizador);

        if (requisicao) {
            requisicao.abort();
        }

        requisicao = null;
        numeroBusca++;
    }

    function mensagemCidade(texto) {
        sugestoes.replaceChildren();

        const aviso = document.createElement('div');
        aviso.className = 'editar-perfil-aviso-cidade';
        aviso.textContent = texto;

        sugestoes.appendChild(aviso);
        sugestoes.hidden = false;
    }

    async function buscarCidades(termo) {
        if (requisicao) {
            requisicao.abort();
        }

        requisicao = new AbortController();

        const atual = ++numeroBusca;

        try {
            const url = new URL(
                form.dataset.urlCidades,
                window.location.origin
            );

            url.searchParams.set('busca', termo);

            const resposta = await fetch(url.toString(), {
                signal: requisicao.signal,
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error(`HTTP ${resposta.status}`);
            }

            const cidades = await resposta.json();

            if (atual !== numeroBusca) return;

            sugestoes.replaceChildren();

            if (!Array.isArray(cidades) || cidades.length === 0) {
                mensagemCidade('Nenhuma cidade encontrada.');
                return;
            }

            cidades.forEach(item => {
                const botao = document.createElement('button');

                botao.type = 'button';
                botao.setAttribute('role', 'option');

                botao.textContent = `${item.nome} - ${item.uf}`;

                botao.addEventListener('click', () => {
                    cancelarBusca();

                    cidadeBusca.value = `${item.nome} - ${item.uf}`;
                    cidadeId.value = String(item.codigo_ibge);

                    fecharSugestoes();
                });

                sugestoes.appendChild(botao);
            });

            sugestoes.hidden = false;

        } catch (erro) {
            if (
                erro.name === 'AbortError' ||
                atual !== numeroBusca
            ) {
                return;
            }

            console.error('Erro ao consultar cidades:', erro);

            mensagemCidade(
                'Não foi possível buscar cidades. Tente novamente.'
            );
        }
    }

    cidadeBusca.addEventListener('input', () => {
        cancelarBusca();

        // O código IBGE só é válido após selecionar uma sugestão.
        cidadeId.value = '';

        fecharSugestoes();

        const termo = cidadeBusca.value.trim();

        if (termo.length < 2) return;

        temporizador = setTimeout(() => {
            buscarCidades(termo);
        }, 300);
    });

    cidadeBusca.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') {
            fecharSugestoes();
        }
    });

    document.addEventListener('click', evento => {
        if (!evento.target.closest('.campo-cidade')) {
            fecharSugestoes();
        }
    });

    // ==========================================
    // 2. MÁSCARA DO CPF
    // ==========================================

    function formatarCpf(valor) {
        const numeros = valor
            .replace(/\D/g, '')
            .slice(0, 11);

        return numeros
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2');
    }

    cpf.addEventListener('input', () => {
        cpf.value = formatarCpf(cpf.value);
    });

    // ==========================================
    // 3. MOSTRAR / OCULTAR SENHA
    // ==========================================

    mostrarSenha.addEventListener('click', () => {
        const visivel = senha.type === 'text';

        senha.type = visivel ? 'password' : 'text';

        mostrarSenha.textContent = visivel
            ? 'Mostrar'
            : 'Ocultar';

        mostrarSenha.setAttribute(
            'aria-label',
            visivel ? 'Mostrar senha' : 'Ocultar senha'
        );
    });

    // ==========================================
    // 4. VALIDAÇÃO ANTES DE SALVAR
    // ==========================================

    form.addEventListener('submit', evento => {
        if (!cidadeId.value) {
            evento.preventDefault();

            alert(
                'Selecione uma cidade válida na lista de sugestões.'
            );

            cidadeBusca.focus();
            return;
        }

        if (cpf.value.replace(/\D/g, '').length !== 11) {
            evento.preventDefault();

            alert('O CPF deve conter 11 números.');

            cpf.focus();
            return;
        }

        // Evita o envio duplicado do formulário.
        if (form.dataset.enviando === '1') {
            evento.preventDefault();
            return;
        }

        form.dataset.enviando = '1';

        botaoSalvar.disabled = true;
        botaoSalvar.textContent = 'Salvando...';
    });
});
