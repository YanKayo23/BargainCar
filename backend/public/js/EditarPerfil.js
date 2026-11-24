
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
            cancelarBusca();
            fecharSugestoes();
        }
    });

    document.addEventListener('click', evento => {
        if (!evento.target.closest('.campo-cidade')) {
            cancelarBusca();
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
    // 4. FOTO DE PERFIL
    // ==========================================

    const inputFoto = document.getElementById('foto_perfil');
    const fotoPrevia = document.getElementById('fotoPrevia');
    const fotoInicial = document.getElementById('fotoInicial');
    const campoRemover = document.getElementById('remover_foto');
    const removerFoto = document.getElementById('removerFoto');
    const desfazerFoto = document.getElementById('desfazerFoto');
    const campoNome = document.getElementById('nome');

    const fotoOriginal = fotoPrevia.getAttribute('src');

    const possuiFotoOriginal =
        Boolean(fotoOriginal) && !fotoPrevia.hidden;

    let urlTemporaria = null;

    const formatosPermitidos = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    const tamanhoMaximo = 2 * 1024 * 1024;

    function fotoValida(arquivo) {
        return formatosPermitidos.includes(arquivo.type)
            && arquivo.size > 0
            && arquivo.size <= tamanhoMaximo;
    }

    // Atualiza a inicial conforme o nome digitado.

    function atualizarInicial() {
        const nome = campoNome.value.trim();

        fotoInicial.textContent = nome
            ? Array.from(nome)[0].toUpperCase()
            : 'U';
    }

    campoNome.addEventListener('input', atualizarInicial);

    atualizarInicial();

    // Libera a imagem temporária da memória.

    function limparPreviaTemporaria() {
        if (urlTemporaria) {
            URL.revokeObjectURL(urlTemporaria);
            urlTemporaria = null;
        }
    }

    // Mostra a foto de perfil.

    function mostrarFoto(url) {
        fotoPrevia.src = url;
        fotoPrevia.hidden = false;
        fotoInicial.hidden = true;
    }

    // Mostra a inicial no lugar da foto.

    function mostrarInicial() {
        atualizarInicial();

        fotoPrevia.hidden = true;
        fotoPrevia.removeAttribute('src');

        fotoInicial.hidden = false;
    }

    // Restaura a foto cadastrada anteriormente.

    function restaurarFotoOriginal() {
        limparPreviaTemporaria();

        inputFoto.value = '';
        campoRemover.value = '0';

        if (possuiFotoOriginal) {
            mostrarFoto(fotoOriginal);
        } else {
            mostrarInicial();
        }

        removerFoto.hidden = !possuiFotoOriginal;
        desfazerFoto.hidden = true;
    }

    // ==========================================
    // 5. SELECIONAR UMA NOVA FOTO
    // ==========================================

    inputFoto.addEventListener('change', () => {
        limparPreviaTemporaria();

        const arquivo = inputFoto.files?.[0];

        if (!arquivo) {
            if (campoRemover.value === '1') {
                mostrarInicial();
            } else {
                restaurarFotoOriginal();
            }

            return;
        }

        if (!fotoValida(arquivo)) {
            alert(
                'Escolha uma imagem JPG, PNG ou WebP de até 2 MB.'
            );

            restaurarFotoOriginal();

            return;
        }

        // Selecionar uma foto cancela a remoção anterior.

        campoRemover.value = '0';

        // A prévia é criada localmente.
        // Nenhum arquivo é enviado neste momento.

        urlTemporaria = URL.createObjectURL(arquivo);

        mostrarFoto(urlTemporaria);

        removerFoto.hidden = false;
        desfazerFoto.hidden = true;
    });

    // ==========================================
    // 6. REMOVER FOTO
    // ==========================================

    removerFoto.addEventListener('click', () => {
        limparPreviaTemporaria();

        inputFoto.value = '';

        // Marca a foto para remoção ao salvar.

        campoRemover.value = possuiFotoOriginal ? '1' : '0';

        mostrarInicial();

        removerFoto.hidden = true;
        desfazerFoto.hidden = !possuiFotoOriginal;
    });

    // ==========================================
    // 7. DESFAZER REMOÇÃO
    // ==========================================

    desfazerFoto.addEventListener('click', () => {
        restaurarFotoOriginal();
    });

    // ==========================================
    // 8. VALIDAÇÃO ANTES DE SALVAR
    // ==========================================

    form.addEventListener('submit', evento => {
        cancelarBusca();
        fecharSugestoes();

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

        const arquivo = inputFoto.files?.[0];

        if (arquivo && !fotoValida(arquivo)) {
            evento.preventDefault();

            alert(
                'A foto deve ser JPG, PNG ou WebP de até 2 MB.'
            );

            inputFoto.focus();

            return;
        }

        // Evita o envio duplicado do formulário.

        if (form.dataset.enviando === '1') {
            evento.preventDefault();

            return;
        }

        form.dataset.enviando = '1';

        botaoSalvar.disabled = true;
        botaoSalvar.textContent = 'Salvando alterações...';

        // Envia a foto e os dados pessoais juntos.
    });
});
