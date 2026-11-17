
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('formEditarAnuncio');

    if (!form) {
        return;
    }

    // =========================================
    // ELEMENTOS DO FORMULÁRIO
    // =========================================

    const cidade = document.getElementById('cidade_busca');
    const cidadeId = document.getElementById('cidade_id');
    const sugestoes = document.getElementById('sugestoesCidades');

    const inputFotos = document.getElementById('fotos');
    const preview = document.getElementById('previewFotos');
    const contador = document.getElementById('contadorFotos');

    const inputVideo = document.getElementById('video');
    const nomeVideo = document.getElementById('nomeVideo');

    const salvar = document.getElementById('botaoSalvar');

    // =========================================
    // CONFIGURAÇÕES
    // =========================================

    const MB = 1024 * 1024;

    const MIN_FOTOS = 3;
    const MAX_FOTOS = 5;

    const MAX_TAMANHO_FOTO = 5 * MB;
    const MAX_TAMANHO_VIDEO = 50 * MB;

    const tiposFotosPermitidos = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    // =========================================
    // CONTROLE DA BUSCA DE CIDADES
    // =========================================

    let buscaNumero = 0;
    let temporizador = null;
    let controlador = null;

    function fecharSugestoes() {
        sugestoes.replaceChildren();
        sugestoes.hidden = true;
    }

    function mostrarMensagemCidades(mensagem) {

        sugestoes.replaceChildren();

        const aviso = document.createElement('div');

        aviso.className = 'mensagem-cidades';
        aviso.textContent = mensagem;

        sugestoes.appendChild(aviso);
        sugestoes.hidden = false;
    }

    function cancelarBusca() {

        clearTimeout(temporizador);

        controlador?.abort();
        controlador = null;

        buscaNumero++;
    }

    // =========================================
    // SELECIONAR CIDADE
    // =========================================

    function selecionarCidade(item) {

        cancelarBusca();

        cidade.value = `${item.nome} - ${item.uf}`;
        cidadeId.value = String(item.codigo_ibge);

        fecharSugestoes();
    }

    // =========================================
    // BUSCAR CIDADES
    // =========================================

    async function buscarCidades(texto) {

        controlador?.abort();

        controlador = new AbortController();

        const atual = ++buscaNumero;

        try {

            const url = new URL(
                form.dataset.urlCidades,
                window.location.origin
            );

            url.searchParams.set('busca', texto);

            const resposta = await fetch(url.toString(), {
                method: 'GET',
                signal: controlador.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error(
                    `Erro HTTP ${resposta.status} na busca de cidades.`
                );
            }

            const cidades = await resposta.json();

            if (atual !== buscaNumero) {
                return;
            }

            sugestoes.replaceChildren();

            if (
                !Array.isArray(cidades) ||
                cidades.length === 0
            ) {

                mostrarMensagemCidades(
                    'Nenhuma cidade encontrada.'
                );

                return;
            }

            cidades.forEach(item => {

                const botao = document.createElement('button');

                botao.type = 'button';
                botao.className = 'sugestao-cidade';
                botao.setAttribute('role', 'option');

                botao.textContent =
                    `${item.nome} - ${item.uf}`;

                botao.addEventListener('click', () => {
                    selecionarCidade(item);
                });

                sugestoes.appendChild(botao);
            });

            sugestoes.hidden = false;

        } catch (erro) {

            if (erro.name === 'AbortError') {
                return;
            }

            if (atual !== buscaNumero) {
                return;
            }

            console.error(
                'Erro na busca de cidades:',
                erro
            );

            mostrarMensagemCidades(
                'Não foi possível buscar as cidades. Verifique a rota /cidades.'
            );
        }
    }

    // =========================================
    // DIGITAÇÃO NO CAMPO CIDADE
    // =========================================

    cidade.addEventListener('input', () => {

        cancelarBusca();

        // Uma cidade digitada ainda não foi selecionada.
        cidadeId.value = '';

        fecharSugestoes();

        const texto = cidade.value.trim();

        if (texto.length < 2) {
            return;
        }

        if (texto.length > 100) {

            mostrarMensagemCidades(
                'Digite no máximo 100 caracteres.'
            );

            return;
        }

        temporizador = setTimeout(() => {
            buscarCidades(texto);
        }, 300);
    });

    // =========================================
    // FECHAR SUGESTÕES
    // =========================================

    document.addEventListener('click', evento => {

        if (!evento.target.closest('.campo-cidade')) {
            fecharSugestoes();
        }
    });

    cidade.addEventListener('keydown', evento => {

        if (evento.key === 'Escape') {
            fecharSugestoes();
        }
    });

    // =========================================
    // QUANTIDADE FINAL DE FOTOGRAFIAS
    // =========================================

    function quantidadeFinal() {

        const atuais = Number(
            form.dataset.fotosExistentes || 0
        );

        const removidas = form.querySelectorAll(
            'input[name="remover_fotos[]"]:checked'
        ).length;

        const novas = inputFotos.files.length;

        return atuais - removidas + novas;
    }

    // =========================================
    // VALIDAÇÃO DAS FOTOGRAFIAS
    // =========================================

    function validarFotos() {

        const total = quantidadeFinal();

        if (
            total < MIN_FOTOS ||
            total > MAX_FOTOS
        ) {

            return (
                'O anúncio deve ficar com no mínimo 3 ' +
                'e no máximo 5 fotografias.'
            );
        }

        for (const foto of inputFotos.files) {

            if (!tiposFotosPermitidos.includes(foto.type)) {

                return (
                    `A foto "${foto.name}" possui ` +
                    'um formato não permitido.'
                );
            }

            if (foto.size === 0) {

                return (
                    `A foto "${foto.name}" está vazia.`
                );
            }

            if (foto.size > MAX_TAMANHO_FOTO) {

                return (
                    `A foto "${foto.name}" ultrapassa ` +
                    'o limite de 5 MB.'
                );
            }
        }

        return null;
    }

    // =========================================
    // PRÉ-VISUALIZAÇÃO DAS NOVAS FOTOS
    // =========================================

    function atualizarFotos() {

        const total = quantidadeFinal();

        contador.textContent =
            `${total} foto(s) após salvar ` +
            '(mínimo 3, máximo 5).';

        preview.replaceChildren();

        Array.from(inputFotos.files).forEach(foto => {

            if (!tiposFotosPermitidos.includes(foto.type)) {
                return;
            }

            const imagem = document.createElement('img');

            imagem.className = 'preview-foto';
            imagem.alt = `Nova foto: ${foto.name}`;

            const urlTemporaria = URL.createObjectURL(foto);

            imagem.onload = () => {
                URL.revokeObjectURL(urlTemporaria);
            };

            imagem.onerror = () => {
                URL.revokeObjectURL(urlTemporaria);
            };

            imagem.src = urlTemporaria;

            preview.appendChild(imagem);
        });
    }

    inputFotos.addEventListener('change', atualizarFotos);

    form.querySelectorAll(
        'input[name="remover_fotos[]"]'
    ).forEach(input => {

        input.addEventListener('change', atualizarFotos);
    });

    atualizarFotos();

    // =========================================
    // VALIDAÇÃO DO VÍDEO
    // =========================================

    function validarVideo() {

        const video = inputVideo.files[0];

        if (!video) {
            return null;
        }

        const extensaoMp4 = /\.mp4$/i.test(video.name);

        if (
            video.type !== 'video/mp4' ||
            !extensaoMp4
        ) {

            return 'O vídeo deve estar no formato MP4.';
        }

        if (video.size === 0) {

            return 'O vídeo selecionado está vazio.';
        }

        if (video.size > MAX_TAMANHO_VIDEO) {

            return 'O vídeo ultrapassa o limite de 50 MB.';
        }

        return null;
    }

    inputVideo.addEventListener('change', () => {

        const video = inputVideo.files[0];

        if (!video) {

            nomeVideo.textContent = '';
            return;
        }

        const erro = validarVideo();

        nomeVideo.textContent = erro || video.name;
    });

    // =========================================
    // VALIDAÇÃO FINAL E SALVAMENTO
    // =========================================

    form.addEventListener('submit', evento => {

        // Verifica se uma cidade real foi selecionada.
        if (!cidadeId.value) {

            evento.preventDefault();

            alert(
                'Selecione uma cidade válida na lista de sugestões.'
            );

            cidade.focus();

            return;
        }

        // Verifica a quantidade final de fotos.
        const erroFotos = validarFotos();

        if (erroFotos) {

            evento.preventDefault();

            alert(erroFotos);

            inputFotos.focus();

            return;
        }

        // Verifica o vídeo opcional.
        const erroVideo = validarVideo();

        if (erroVideo) {

            evento.preventDefault();

            alert(erroVideo);

            inputVideo.focus();

            return;
        }

        // Impede múltiplos envios.
        if (form.dataset.enviando === '1') {

            evento.preventDefault();
            return;
        }

        form.dataset.enviando = '1';

        if (salvar) {

            salvar.disabled = true;
            salvar.textContent = 'Salvando...';
        }
    });

});
