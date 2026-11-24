document.addEventListener('DOMContentLoaded', async () => {

    const botoes = [
        ...document.querySelectorAll('[data-fav-id]')
    ];

    if (!botoes.length) return;

    let estado;
    let ocupado = false;
    let timer;

    // ==========================================
    // MENSAGENS FLUTUANTES
    // ==========================================

    function aviso(texto, erro = false) {

        let toast = document.querySelector('.bc-fav-toast');

        if (!toast) {
            toast = document.createElement('div');
            toast.setAttribute('role', 'status');
            document.body.appendChild(toast);
        }

        toast.className = 'bc-fav-toast' + (erro ? ' erro' : '');
        toast.textContent = texto;

        clearTimeout(timer);

        timer = setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // ==========================================
    // ESTADO VISUAL DO CORAÇÃO
    // ==========================================

    function pintar(botao, ativo) {

        botao.setAttribute(
            'aria-pressed',
            String(ativo)
        );

        botao.setAttribute(
            'aria-label',
            ativo
                ? 'Remover dos favoritos'
                : 'Adicionar aos favoritos'
        );

        botao.title = botao.getAttribute('aria-label');
    }

    // ==========================================
    // SEGURANÇA CSRF
    // ==========================================

    function atualizarToken(dados) {

        if (!dados.csrfNome || !dados.csrfHash) return;

        estado.csrfNome = dados.csrfNome;
        estado.csrfHash = dados.csrfHash;

        document.querySelectorAll(
            'input[type="hidden"]'
        ).forEach(campo => {

            if (campo.name === estado.csrfNome) {
                campo.value = estado.csrfHash;
            }

        });
    }

    // ==========================================
    // CARREGAR FAVORITOS DO USUÁRIO
    // ==========================================

    async function carregar() {

        const resposta = await fetch(
            botoes[0].dataset.favEstado,
            {
                cache: 'no-store',
                headers: {
                    Accept: 'application/json'
                }
            }
        );

        if (!resposta.ok) {
            throw new Error(
                'Falha ao consultar favoritos.'
            );
        }

        estado = await resposta.json();

        atualizarToken(estado);

        botoes.forEach(botao => {

            const id = Number(botao.dataset.favId);

            pintar(botao, estado.ids.includes(id));

            botao.disabled = false;

        });
    }

    try {
        await carregar();
    } catch {
        aviso(
            'Não foi possível carregar os favoritos.',
            true
        );

        return;
    }

    // ==========================================
    // ADICIONAR OU REMOVER FAVORITOS
    // ==========================================

    botoes.forEach(botao => {

        botao.addEventListener('click', async () => {

            if (ocupado) return;

            // Visitante precisa entrar na conta.

            if (!estado.logado) {
                location.assign(estado.login);
                return;
            }

            ocupado = true;

            botoes.forEach(b => b.disabled = true);

            try {

                const corpo = new URLSearchParams();

                corpo.set(
                    estado.csrfNome,
                    estado.csrfHash
                );

                const resposta = await fetch(
                    botao.dataset.favUrl,
                    {
                        method: 'POST',
                        body: corpo,
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (resposta.redirected) {
                    location.assign(estado.login);
                    return;
                }

                const dados = await resposta.json();

                atualizarToken(dados);

                if (resposta.status === 401) {
                    location.assign(estado.login);
                    return;
                }

                if (!resposta.ok) {

                    throw new Error(
                        dados.mensagem ||
                        'Não foi possível atualizar os favoritos.'
                    );
                }

                const id = Number(botao.dataset.favId);

                estado.ids = estado.ids.filter(
                    x => x !== id
                );

                if (dados.favoritado) {
                    estado.ids.push(id);
                }

                // Atualiza os corações do mesmo anúncio.

                botoes.filter(
                    b => Number(b.dataset.favId) === id
                ).forEach(b => {

                    pintar(b, dados.favoritado);

                });

                // Remove o card na página de favoritos.

                if (!dados.favoritado) {

                    botao.closest(
                        '[data-fav-card]'
                    )?.remove();

                    const total = document.getElementById(
                        'bc-fav-total'
                    );

                    if (total) {

                        const quantidade =
                            document.querySelectorAll(
                                '[data-fav-card]'
                            ).length;

                        total.textContent =
                            `${quantidade} anúncio(s) salvo(s)`;

                        document.getElementById(
                            'bc-fav-vazio'
                        ).hidden = quantidade !== 0;
                    }
                }

                aviso(dados.mensagem);

            } catch (erro) {

                aviso(
                    erro.message ||
                    'Erro ao atualizar favoritos.',
                    true
                );

                try {
                    await carregar();
                } catch {
                    // Mantém o último estado conhecido.
                }

            } finally {

                ocupado = false;

                botoes.forEach(
                    b => b.disabled = false
                );
            }

        });

    });

});
