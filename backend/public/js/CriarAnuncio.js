
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('formCriarAnuncio');

    if (!form) {
        return;
    }

    // =========================================
    // ELEMENTOS DO FORMULÁRIO
    // =========================================

    const inputCidade = document.getElementById('cidade_busca');
    const inputCidadeId = document.getElementById('cidade_id');
    const sugestoes = document.getElementById('sugestoesCidades');

    const inputContato = document.getElementById('contato');
    const informacoesLoja = document.getElementById('informacoesLoja');

    const radiosAnunciante = document.querySelectorAll(
        'input[name="tipo_anunciante"]'
    );

    const inputFotos = document.getElementById('fotos');
    const contadorFotos = document.getElementById('contadorFotos');
    const previewFotos = document.getElementById('previewFotos');

    const inputVideo = document.getElementById('video');
    const nomeVideo = document.getElementById('nomeVideo');

    const botaoPublicar = document.getElementById('botaoPublicar');

    // =========================================
    // DADOS DO USUÁRIO E DA LOJA
    // =========================================

    const cidadePessoa = form.dataset.cidadePessoa || '';
    const cidadeLoja = form.dataset.cidadeLoja || '';

    const nomeCidadePessoa =
        form.dataset.nomeCidadePessoa || '';

    const nomeCidadeLoja =
        form.dataset.nomeCidadeLoja || '';

    const contatoPessoa =
        form.dataset.contatoPessoa || '';

    const contatoLoja =
        form.dataset.contatoLoja || '';

    const urlCidades = form.dataset.urlCidades;

    // =========================================
    // CONTROLE DAS PESQUISAS
    // =========================================

    let temporizadorBusca = null;
    let controladorBusca = null;
    let numeroBusca = 0;

    function cancelarBusca() {
        clearTimeout(temporizadorBusca);

        controladorBusca?.abort();
        controladorBusca = null;

        numeroBusca++;
    }

    function fecharSugestoes() {
        sugestoes.replaceChildren();
        sugestoes.hidden = true;
    }

    function mostrarMensagemCidades(mensagem) {
        sugestoes.replaceChildren();

        const elemento = document.createElement('div');

        elemento.className = 'mensagem-cidades';
        elemento.textContent = mensagem;

        sugestoes.appendChild(elemento);
        sugestoes.hidden = false;
    }

    // =========================================
    // SELEÇÃO DE CIDADE
    // =========================================

    function selecionarCidade(cidade) {
        cancelarBusca();

        inputCidade.value = `${cidade.nome} - ${cidade.uf}`;
        inputCidadeId.value = String(cidade.codigo_ibge);

        fecharSugestoes();
    }

    // =========================================
    // BUSCA DE CIDADES
    // =========================================

    async function buscarCidades(texto) {
        controladorBusca?.abort();

        const controlador = new AbortController();

        controladorBusca = controlador;

        const buscaAtual = ++numeroBusca;

        try {
            const url = new URL(
                urlCidades,
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
                throw new Error('Erro na consulta de cidades.');
            }

            const cidades = await resposta.json();

            if (buscaAtual !== numeroBusca) {
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

            cidades.forEach(cidade => {

                const botao = document.createElement('button');

                botao.type = 'button';
                botao.className = 'sugestao-cidade';

                botao.setAttribute('role', 'option');

                botao.textContent =
                    `${cidade.nome} - ${cidade.uf}`;

                botao.addEventListener('click', () => {
                    selecionarCidade(cidade);
                });

                sugestoes.appendChild(botao);
            });

            sugestoes.hidden = false;

        } catch (erro) {

            if (erro.name === 'AbortError') {
                return;
            }

            if (buscaAtual !== numeroBusca) {
                return;
            }

            mostrarMensagemCidades(
                'Não foi possível buscar as cidades.'
            );
        }
    }

    // =========================================
    // DIGITAÇÃO NO CAMPO CIDADE
    // =========================================

    inputCidade.addEventListener('input', () => {

        cancelarBusca();

        // Uma cidade digitada não é uma cidade selecionada.
        inputCidadeId.value = '';

        fecharSugestoes();

        const texto = inputCidade.value.trim();

        if (texto.length < 2) {
            return;
        }

        if (texto.length > 100) {
            mostrarMensagemCidades(
                'Digite no máximo 100 caracteres.'
            );

            return;
        }

        temporizadorBusca = setTimeout(() => {
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

    inputCidade.addEventListener('keydown', evento => {

        if (evento.key === 'Escape') {
            fecharSugestoes();
        }
    });

    // =========================================
    // ALTERNAR ENTRE PESSOA FÍSICA E LOJA
    // =========================================

    function atualizarTipoAnunciante(tipo) {

        cancelarBusca();
        fecharSugestoes();

        const anunciarComoLoja = tipo === 'loja';

        if (informacoesLoja) {
            informacoesLoja.hidden = !anunciarComoLoja;
        }

        inputCidadeId.value = anunciarComoLoja
            ? cidadeLoja
            : cidadePessoa;

        inputCidade.value = anunciarComoLoja
            ? nomeCidadeLoja
            : nomeCidadePessoa;

        inputContato.value = anunciarComoLoja
            ? contatoLoja
            : contatoPessoa;
    }

    radiosAnunciante.forEach(radio => {

        radio.addEventListener('change', () => {

            if (!radio.checked) {
                return;
            }

            atualizarTipoAnunciante(radio.value);
        });
    });

    // =========================================
    // CONFIGURAÇÕES DAS MÍDIAS
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
    // VALIDAÇÃO DAS FOTOS
    // =========================================

    function validarFotos() {

        const fotos = Array.from(inputFotos.files);

        if (
            fotos.length < MIN_FOTOS ||
            fotos.length > MAX_FOTOS
        ) {
            return 'Selecione de 3 a 5 fotos do veículo.';
        }

        for (const foto of fotos) {

            if (!tiposFotosPermitidos.includes(foto.type)) {
                return (
                    `A foto "${foto.name}" possui ` +
                    'um formato não permitido.'
                );
            }

            if (foto.size > MAX_TAMANHO_FOTO) {
                return (
                    `A foto "${foto.name}" ultrapassa ` +
                    'o limite de 5 MB.'
                );
            }

            if (foto.size === 0) {
                return (
                    `A foto "${foto.name}" está vazia.`
                );
            }
        }

        return null;
    }

    // =========================================
    // PRÉ-VISUALIZAÇÃO DAS FOTOS
    // =========================================

    function atualizarPreviewFotos() {

        const fotos = Array.from(inputFotos.files);

        previewFotos.replaceChildren();

        if (fotos.length === 0) {

            contadorFotos.textContent =
                'Nenhuma foto selecionada.';

            return;
        }

        contadorFotos.textContent =
            `${fotos.length} foto(s) selecionada(s).`;

        fotos.forEach(foto => {

            if (!tiposFotosPermitidos.includes(foto.type)) {
                return;
            }

            const imagem = document.createElement('img');

            imagem.alt = `Prévia de ${foto.name}`;
            imagem.className = 'preview-foto';

            const urlTemporaria = URL.createObjectURL(foto);

            imagem.onload = () => {
                URL.revokeObjectURL(urlTemporaria);
            };

            imagem.onerror = () => {
                URL.revokeObjectURL(urlTemporaria);
            };

            imagem.src = urlTemporaria;

            previewFotos.appendChild(imagem);
        });
    }

    inputFotos.addEventListener('change', () => {

        atualizarPreviewFotos();

        const erro = validarFotos();

        if (erro) {
            contadorFotos.textContent = erro;
        }
    });

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

        if (video.size > MAX_TAMANHO_VIDEO) {
            return 'O vídeo ultrapassa o limite de 50 MB.';
        }

        if (video.size === 0) {
            return 'O vídeo selecionado está vazio.';
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
    // VALIDAÇÃO FINAL DO FORMULÁRIO
    // =========================================

    form.addEventListener('submit', evento => {

        // Confirma que foi selecionada uma cidade real
        if (!inputCidadeId.value) {

            evento.preventDefault();

            alert(
                'Selecione uma cidade válida na lista de sugestões.'
            );

            inputCidade.focus();

            return;
        }

        // Confere a quantidade e os formatos das fotos
        const erroFotos = validarFotos();

        if (erroFotos) {

            evento.preventDefault();

            alert(erroFotos);

            inputFotos.focus();

            return;
        }

        // Confere o vídeo opcional
        const erroVideo = validarVideo();

        if (erroVideo) {

            evento.preventDefault();

            alert(erroVideo);

            inputVideo.focus();

            return;
        }

        // Evita múltiplos envios do mesmo formulário
        if (form.dataset.enviando === '1') {

            evento.preventDefault();

            return;
        }

        form.dataset.enviando = '1';

        if (botaoPublicar) {
            botaoPublicar.disabled = true;
            botaoPublicar.textContent = 'Publicando...';
        }
    });

});
