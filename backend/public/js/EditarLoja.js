
/* BargainCar - Editar dados e imagens da loja */
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
    // AUTOCOMPLETE DE CIDADES
    // ==========================================

    function fechar() {
        lista.hidden = true;
        lista.replaceChildren();
        busca.setAttribute('aria-expanded', 'false');
    }

    function cancelar() {
        clearTimeout(timer);
        controlador?.abort();
        sequencia++;
    }

    function aviso(texto) {
        lista.replaceChildren();

        const p = document.createElement('p');
        p.textContent = texto;
        lista.appendChild(p);

        lista.hidden = false;
        busca.setAttribute('aria-expanded', 'true');
    }

    async function procurar(termo) {
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
                aviso('Nenhuma cidade encontrada.');
                return;
            }

            for (const cidade of cidades) {
                const botao = document.createElement('button');

                botao.type = 'button';
                botao.setAttribute('role', 'option');
                botao.textContent =
                    `${cidade.nome} - ${cidade.uf}`;

                botao.addEventListener('click', () => {
                    cancelar();

                    busca.value =
                        `${cidade.nome} - ${cidade.uf}`;

                    cidadeId.value =
                        String(cidade.codigo_ibge);

                    fechar();
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
                    'Falha ao buscar cidades:',
                    erro
                );

                aviso(
                    'Não foi possível buscar cidades. Tente novamente.'
                );
            }
        }
    }

    busca.addEventListener('input', () => {
        cancelar();

        cidadeId.value = '';
        fechar();

        const termo = busca.value.trim();

        if (termo.length >= 2) {
            timer = setTimeout(() => procurar(termo), 300);
        }
    });

    busca.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') fechar();
    });

    document.addEventListener('click', evento => {
        if (!campoCidade.contains(evento.target)) {
            fechar();
        }
    });

    form.addEventListener('submit', evento => {
        if (!cidadeId.value) {
            evento.preventDefault();

            alert(
                'Selecione uma cidade válida na lista de sugestões.'
            );

            busca.focus();
            return;
        }

        if (form.dataset.enviando === '1') {
            evento.preventDefault();
            return;
        }

        form.dataset.enviando = '1';

        salvar.disabled = true;
        salvar.textContent = 'Salvando...';
    });

    // ==========================================
    // FOTOS DE PERFIL E FUNDO
    // ==========================================

    document.querySelectorAll('.el-form-imagem').forEach(formImagem => {
        const input = formImagem.querySelector('input[type="file"]');
        const botao = formImagem.querySelector('button[type="submit"]');

        const previa = document.getElementById(input.dataset.previa);
        const vazio = document.getElementById(input.dataset.vazio);

        let urlTemporaria = null;
        let arquivoValido = false;

        input.addEventListener('change', () => {
            if (urlTemporaria) {
                URL.revokeObjectURL(urlTemporaria);
            }

            urlTemporaria = null;
            arquivoValido = false;

            const arquivo = input.files?.[0];

            if (!arquivo) return;

            const formatos = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (
                !formatos.includes(arquivo.type) ||
                arquivo.size > 5 * 1024 * 1024
            ) {
                alert(
                    'Escolha uma imagem JPG, PNG ou WebP de até 5 MB.'
                );

                input.value = '';
                return;
            }

            arquivoValido = true;
            urlTemporaria = URL.createObjectURL(arquivo);

            previa.src = urlTemporaria;
            previa.hidden = false;
            vazio.hidden = true;
        });

        formImagem.addEventListener('submit', evento => {
            if (
                !arquivoValido ||
                formImagem.dataset.enviando === '1'
            ) {
                evento.preventDefault();
                return;
            }

            formImagem.dataset.enviando = '1';

            botao.disabled = true;
            botao.textContent = 'Enviando imagem...';
        });
    });
});
