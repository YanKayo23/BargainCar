
/* BargainCar - Edição dos dados e imagens da loja */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEditarLoja');

    if (!form) return;

    const busca = document.getElementById('cidade_busca');
    const cidadeId = document.getElementById('cidade_id');
    const lista = document.getElementById('el-sugestoes');
    const salvar = document.getElementById('el-salvar');
    const campoCidade = document.getElementById('el-campo-cidade');

    let timer;
    let controlador;
    let sequencia = 0;

    // ==========================================
    // 1. AUTOCOMPLETE DE CIDADES
    // ==========================================

    function fecharSugestoes() {
        lista.hidden = true;
        lista.replaceChildren();

        busca.setAttribute('aria-expanded', 'false');
    }

    function cancelarBusca() {
        clearTimeout(timer);
        controlador?.abort();
        sequencia++;
    }

    function avisoCidade(texto) {
        lista.replaceChildren();

        const aviso = document.createElement('p');
        aviso.textContent = texto;

        lista.appendChild(aviso);
        lista.hidden = false;

        busca.setAttribute('aria-expanded', 'true');
    }

    async function buscarCidades(termo) {
        controlador?.abort();

        controlador = new AbortController();

        const atual = ++sequencia;

        try {
            const url = new URL(
                form.dataset.urlCidades,
                window.location.href
            );

            url.searchParams.set('busca', termo);

            const resposta = await fetch(url, {
                signal: controlador.signal,
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error(`HTTP ${resposta.status}`);
            }

            const cidades = await resposta.json();

            if (atual !== sequencia) return;

            lista.replaceChildren();

            if (!Array.isArray(cidades) || cidades.length === 0) {
                avisoCidade('Nenhuma cidade encontrada.');
                return;
            }

            for (const cidade of cidades) {
                const botao = document.createElement('button');

                botao.type = 'button';
                botao.setAttribute('role', 'option');

                botao.textContent =
                    `${cidade.nome} - ${cidade.uf}`;

                botao.addEventListener('click', () => {
                    cancelarBusca();

                    busca.value =
                        `${cidade.nome} - ${cidade.uf}`;

                    cidadeId.value =
                        String(cidade.codigo_ibge);

                    fecharSugestoes();
                });

                lista.appendChild(botao);
            }

            lista.hidden = false;

            busca.setAttribute('aria-expanded', 'true');

        } catch (erro) {
            if (
                erro.name !== 'AbortError' &&
                atual === sequencia
            ) {
                console.error(
                    'Erro ao buscar cidades:',
                    erro
                );

                avisoCidade(
                    'Não foi possível buscar cidades. Tente novamente.'
                );
            }
        }
    }

    busca.addEventListener('input', () => {
        cancelarBusca();

        cidadeId.value = '';

        fecharSugestoes();

        const termo = busca.value.trim();

        if (termo.length >= 2) {
            timer = setTimeout(() => {
                buscarCidades(termo);
            }, 300);
        }
    });

    busca.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') {
            cancelarBusca();
            fecharSugestoes();
        }
    });

    document.addEventListener('click', evento => {
        if (!campoCidade.contains(evento.target)) {
            cancelarBusca();
            fecharSugestoes();
        }
    });

    // ==========================================
    // 2. VALIDAÇÃO DAS IMAGENS
    // ==========================================

    const formatosPermitidos = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    const tamanhoMaximo = 5 * 1024 * 1024;

    function imagemValida(arquivo) {
        return formatosPermitidos.includes(arquivo.type)
            && arquivo.size > 0
            && arquivo.size <= tamanhoMaximo;
    }

    // ==========================================
    // 3. GERENCIAMENTO DAS IMAGENS
    // ==========================================

    function configurarImagem(tipo) {
        const input = document.getElementById(
            `el-imagem-${tipo}`
        );

        const previa = document.getElementById(
            `el-previa-img-${tipo}`
        );

        const vazio = document.getElementById(
            `el-sem-foto-${tipo}`
        );

        const campoRemover = document.getElementById(
            `el-remover-${tipo}`
        );

        const botaoRemover = document.querySelector(
            `[data-remover="${tipo}"]`
        );

        const botaoDesfazer = document.querySelector(
            `[data-desfazer="${tipo}"]`
        );

        if (
            !input ||
            !previa ||
            !vazio ||
            !campoRemover ||
            !botaoRemover ||
            !botaoDesfazer
        ) {
            return;
        }

        const imagemOriginal = previa.getAttribute('src');

        const possuiImagemOriginal =
            Boolean(imagemOriginal) && !previa.hidden;

        let urlTemporaria = null;

        // Libera a prévia temporária da memória.

        function limparPreviaTemporaria() {
            if (urlTemporaria) {
                URL.revokeObjectURL(urlTemporaria);
                urlTemporaria = null;
            }
        }

        // Exibe a imagem.

        function mostrarImagem(url) {
            previa.src = url;
            previa.hidden = false;
            vazio.hidden = true;
        }

        // Exibe o espaço sem imagem.

        function ocultarImagem() {
            previa.hidden = true;
            previa.removeAttribute('src');
            vazio.hidden = false;
        }

        // Restaura a imagem original.

        function restaurarOriginal() {
            limparPreviaTemporaria();

            input.value = '';
            campoRemover.value = '0';

            if (possuiImagemOriginal) {
                mostrarImagem(imagemOriginal);
            } else {
                ocultarImagem();
            }

            botaoRemover.disabled = !possuiImagemOriginal;
            botaoRemover.hidden = false;
            botaoDesfazer.hidden = true;
        }

        // ======================================
        // SELECIONAR UMA NOVA IMAGEM
        // ======================================

        input.addEventListener('change', () => {
            limparPreviaTemporaria();

            const arquivo = input.files?.[0];

            if (!arquivo) {
                if (campoRemover.value === '1') {
                    ocultarImagem();
                } else {
                    restaurarOriginal();
                }

                return;
            }

            if (!imagemValida(arquivo)) {
                alert(
                    'Escolha uma imagem JPG, PNG ou WebP de até 5 MB.'
                );

                restaurarOriginal();

                return;
            }

            // Uma nova imagem cancela a remoção anterior.

            campoRemover.value = '0';

            urlTemporaria = URL.createObjectURL(arquivo);

            mostrarImagem(urlTemporaria);

            botaoRemover.disabled = false;
            botaoRemover.hidden = false;
            botaoDesfazer.hidden = true;
        });

        // ======================================
        // REMOVER IMAGEM
        // ======================================

        botaoRemover.addEventListener('click', () => {
            limparPreviaTemporaria();

            // Descarta qualquer nova imagem selecionada.

            input.value = '';

            // Marca a imagem para remoção no salvamento.

            campoRemover.value = '1';

            ocultarImagem();

            botaoRemover.hidden = true;
            botaoDesfazer.hidden = false;
        });

        // ======================================
        // DESFAZER REMOÇÃO
        // ======================================

        botaoDesfazer.addEventListener('click', () => {
            restaurarOriginal();
        });
    }

    // Configura as duas imagens separadamente.

    configurarImagem('perfil');
    configurarImagem('capa');

    // ==========================================
    // 4. SALVAR TODAS AS ALTERAÇÕES
    // ==========================================

    form.addEventListener('submit', evento => {
        cancelarBusca();

        fecharSugestoes();

        if (!cidadeId.value) {
            evento.preventDefault();

            alert(
                'Selecione uma cidade válida na lista de sugestões.'
            );

            busca.focus();

            return;
        }

        const camposImagem = form.querySelectorAll(
            'input[type="file"]'
        );

        for (const input of camposImagem) {
            const arquivo = input.files?.[0];

            if (arquivo && !imagemValida(arquivo)) {
                evento.preventDefault();

                alert(
                    'As imagens devem ser JPG, PNG ou WebP de até 5 MB.'
                );

                input.focus();

                return;
            }
        }

        // Impede o envio duplicado.

        if (form.dataset.enviando === '1') {
            evento.preventDefault();

            return;
        }

        form.dataset.enviando = '1';

        salvar.disabled = true;
        salvar.textContent = 'Salvando alterações...';
    });
});
