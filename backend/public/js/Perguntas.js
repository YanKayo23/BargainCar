document.addEventListener('DOMContentLoaded', () => {

    const secao = document.getElementById('bc-perguntas');

    if (!secao) return;

    const conteudo = document.getElementById(
        'bc-perguntas-conteudo'
    );

    const aviso = document.getElementById(
        'bc-perguntas-aviso'
    );

    const url = secao.dataset.url;

    let estado;
    let ocupado = false;

    // ==========================================
    // CRIAÇÃO SEGURA DOS ELEMENTOS
    // ==========================================

    function elemento(tag, texto, classe) {

        const el = document.createElement(tag);

        if (texto != null) {
            el.textContent = texto;
        }

        if (classe) {
            el.className = classe;
        }

        return el;
    }

    // ==========================================
    // PROTEÇÃO CSRF
    // ==========================================

    function token(dados) {

        if (!dados.csrfNome || !dados.csrfHash) return;

        estado.csrfNome = dados.csrfNome;
        estado.csrfHash = dados.csrfHash;

        document.querySelectorAll(
            'input[type=hidden]'
        ).forEach(campo => {

            if (campo.name === dados.csrfNome) {
                campo.value = dados.csrfHash;
            }

        });
    }

    // ==========================================
    // ENVIAR, RESPONDER OU EXCLUIR
    // ==========================================

    async function enviar(destino, texto = '') {

        if (ocupado) return;

        ocupado = true;

        const botoes = [
            ...secao.querySelectorAll('button')
        ];

        botoes.forEach(b => b.disabled = true);

        try {

            const corpo = new URLSearchParams({ texto });

            corpo.set(
                estado.csrfNome,
                estado.csrfHash
            );

            const resposta = await fetch(destino, {

                method: 'POST',
                body: corpo,

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }

            });

            const dados = await resposta.json();

            token(dados);

            if (!resposta.ok) {

                throw new Error(
                    dados.mensagem ||
                    'Não foi possível concluir.'
                );
            }

            await carregar();

            aviso.textContent = dados.mensagem;

        } catch (erro) {

            aviso.textContent =
                erro.message || 'Erro de comunicação.';

        } finally {

            ocupado = false;

            botoes.forEach(b => b.disabled = false);
        }
    }

    // ==========================================
    // FORMULÁRIO DE PERGUNTA OU RESPOSTA
    // ==========================================

    function formulario(destino, titulo) {

        const form = elemento('form');

        const campo = elemento('textarea');

        campo.required = true;
        campo.maxLength = 500;
        campo.placeholder = titulo;

        campo.setAttribute('aria-label', titulo);

        const contador = elemento(
            'small',
            '0/500 caracteres'
        );

        campo.addEventListener('input', () => {

            contador.textContent =
                `${campo.value.length}/500 caracteres`;

        });

        const botao = elemento('button', titulo);

        botao.type = 'submit';

        form.append(campo, contador, botao);

        form.addEventListener('submit', evento => {

            evento.preventDefault();

            if (campo.value.trim()) {

                enviar(
                    destino,
                    campo.value.trim()
                );
            }
        });

        return form;
    }

    // ==========================================
    // CARREGAR PERGUNTAS E RESPOSTAS
    // ==========================================

    async function carregar() {

        const resposta = await fetch(url, {

            cache: 'no-store',

            headers: {
                Accept: 'application/json'
            }

        });

        const dados = await resposta.json();

        if (!resposta.ok) {

            throw new Error(
                dados.mensagem ||
                'Não foi possível carregar as perguntas.'
            );
        }

        estado = dados;

        token(dados);

        conteudo.replaceChildren();

        conteudo.setAttribute('aria-busy', 'false');

        // Formulário para usuários autenticados.

        if (dados.podePerguntar) {

            conteudo.append(
                formulario(url, 'Enviar pergunta')
            );

        } else if (!dados.logado) {

            const link = elemento(
                'a',
                'Entre na sua conta para perguntar'
            );

            link.href = dados.login;

            conteudo.append(link);
        }

        // Nenhuma pergunta cadastrada.

        if (!dados.perguntas.length) {

            conteudo.append(
                elemento('p', 'Nenhuma pergunta ainda.')
            );
        }

        // Exibição das perguntas.

        dados.perguntas.forEach(pergunta => {

            const card = elemento('article');

            card.append(

                elemento('strong', pergunta.nome),

                elemento('small', pergunta.data_pergunta),

                elemento('p', pergunta.pergunta)

            );

            // Exibir resposta existente.

            if (pergunta.resposta !== null) {

                const resposta = elemento(
                    'div',
                    null,
                    'bc-resposta'
                );

                resposta.append(

                    elemento(
                        'strong',
                        'Resposta do anunciante'
                    ),

                    elemento('p', pergunta.resposta),

                    elemento('small', pergunta.data_resposta)

                );

                card.append(resposta);

            } else if (pergunta.podeResponder) {

                // Somente o anunciante pode responder.

                card.append(

                    formulario(
                        `${url}/${pergunta.id}/responder`,
                        'Responder'
                    )

                );

            } else {

                card.append(

                    elemento(
                        'small',
                        'Aguardando resposta do anunciante.'
                    )

                );
            }

            // Exclusão autorizada.

            if (pergunta.podeExcluir) {

                const excluir = elemento(
                    'button',
                    'Excluir pergunta',
                    'bc-excluir'
                );

                excluir.type = 'button';

                excluir.addEventListener('click', () => {

                    const confirmou = confirm(
                        'Excluir esta pergunta e sua eventual resposta?'
                    );

                    if (confirmou) {

                        enviar(
                            `${url}/${pergunta.id}/excluir`
                        );
                    }

                });

                card.append(excluir);
            }

            conteudo.append(card);

        });
    }

    carregar().catch(erro => {

        conteudo.textContent = erro.message;

        conteudo.setAttribute('aria-busy', 'false');

    });

});
