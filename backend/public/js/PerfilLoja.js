/* ==========================================
   BARGAINCAR - PERFIL DA LOJA
========================================== */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. DESATIVAÇÃO DA LOJA
    // ==========================================

    const modalDesativacao = document.getElementById(
        'pl-modal-desativacao'
    );

    const botaoDesativar = document.getElementById(
        'pl-abrir-desativacao'
    );

    const formularioDesativar = document.getElementById(
        'pl-form-desativar'
    );

    botaoDesativar?.addEventListener('click', () => {
        modalDesativacao?.showModal();
    });

    formularioDesativar?.addEventListener('submit', evento => {

        const botaoConfirmar = evento.currentTarget.querySelector(
            'button[type="submit"]'
        );

        if (botaoConfirmar) {

            botaoConfirmar.disabled = true;
            botaoConfirmar.textContent = 'Desativando...';

        }

    });


    // ==========================================
    // 2. FECHAMENTO DOS MODAIS
    // ==========================================

    document.querySelectorAll('[data-fechar]').forEach(botao => {

        botao.addEventListener('click', () => {

            const modal = botao.closest('dialog');

            if (modal?.id !== 'modal-restaurar-anuncio') {
                modal?.close();
            }

        });

    });

    document.querySelectorAll('.pl-modal').forEach(modal => {

        // O modal de restauração possui controle próprio.
        if (modal.id === 'modal-restaurar-anuncio') {
            return;
        }

        modal.addEventListener('click', evento => {

            if (evento.target === modal) {
                modal.close();
            }

        });

    });


    // ==========================================
    // 3. EXCLUSÃO DE ANÚNCIOS
    // ==========================================

    const modalExcluir = document.getElementById(
        'pl-modal-excluir'
    );

    const formularioExcluir = document.getElementById(
        'pl-form-excluir'
    );

    const tituloExcluir = document.getElementById(
        'pl-anuncio-excluir'
    );

    const confirmarExcluir = formularioExcluir?.querySelector(
        'button[type="submit"]'
    );

    document.querySelectorAll('[data-excluir-anuncio]')
        .forEach(botao => {

            botao.addEventListener('click', () => {

                if (!modalExcluir || !formularioExcluir) {
                    return;
                }

                const url = botao.dataset.url;
                const titulo = botao.dataset.titulo;

                if (!url) {
                    return;
                }

                formularioExcluir.action = url;

                if (tituloExcluir) {

                    tituloExcluir.textContent =
                        `Deseja excluir o anúncio "${titulo}"?`;

                }

                if (confirmarExcluir) {

                    confirmarExcluir.disabled = false;
                    confirmarExcluir.textContent = 'Sim, excluir';

                }

                modalExcluir.showModal();

            });

        });

    formularioExcluir?.addEventListener('submit', evento => {

        if (!formularioExcluir.getAttribute('action')) {

            evento.preventDefault();
            return;

        }

        if (confirmarExcluir) {

            confirmarExcluir.disabled = true;
            confirmarExcluir.textContent = 'Excluindo...';

        }

    });

    modalExcluir?.addEventListener('close', () => {

        formularioExcluir?.removeAttribute('action');

        if (confirmarExcluir) {

            confirmarExcluir.disabled = true;
            confirmarExcluir.textContent = 'Sim, excluir';

        }

    });


    // ==========================================
    // 4. FILTRO DO HISTÓRICO DE ANÚNCIOS
    // ==========================================

    const filtroHistorico = document.getElementById(
        'pl-filtro-historico'
    );

    const itensHistorico = document.querySelectorAll(
        '.pl-historico-item'
    );

    const historicoVazio = document.getElementById(
        'pl-historico-vazio'
    );

    function filtrarHistorico() {

        const filtro = filtroHistorico?.value || 'todos';

        let quantidadeVisivel = 0;

        itensHistorico.forEach(item => {

            const situacao = item.dataset.situacao;

            const mostrar =
                filtro === 'todos' || situacao === filtro;

            item.hidden = !mostrar;

            if (mostrar) {
                quantidadeVisivel++;
            }

        });

        if (historicoVazio) {

            historicoVazio.hidden = quantidadeVisivel > 0;

        }

    }

    filtroHistorico?.addEventListener(
        'change',
        filtrarHistorico
    );

    filtrarHistorico();


    // ==========================================
    // 5. MODAL DE RESTAURAÇÃO DE ANÚNCIOS
    // ==========================================

    const modalRestaurar = document.getElementById(
        'modal-restaurar-anuncio'
    );

    const cancelarRestauracao = document.getElementById(
        'restaurar-cancelar'
    );

    const confirmarRestauracao = document.getElementById(
        'restaurar-confirmar'
    );

    let formularioRestauracao = null;
    let restaurando = false;

    if (
        modalRestaurar &&
        cancelarRestauracao &&
        confirmarRestauracao
    ) {

        document.querySelectorAll('.form-restaurar-anuncio')
            .forEach(formulario => {

                formulario.addEventListener('submit', evento => {

                    evento.preventDefault();

                    if (restaurando || modalRestaurar.open) {
                        return;
                    }

                    formularioRestauracao = formulario;

                    confirmarRestauracao.disabled = false;
                    confirmarRestauracao.textContent = 'Sim, restaurar';

                    cancelarRestauracao.disabled = false;

                    modalRestaurar.showModal();

                });

            });


        // Cancelar restauração

        cancelarRestauracao.addEventListener('click', () => {

            if (!restaurando) {
                modalRestaurar.close();
            }

        });


        // Confirmar restauração

        confirmarRestauracao.addEventListener('click', () => {

            if (!formularioRestauracao || restaurando) {
                return;
            }

            restaurando = true;

            confirmarRestauracao.disabled = true;
            confirmarRestauracao.textContent = 'Restaurando...';

            cancelarRestauracao.disabled = true;

            formularioRestauracao.submit();

        });


        // Fechar ao clicar fora do modal

        modalRestaurar.addEventListener('click', evento => {

            if (restaurando || evento.target !== modalRestaurar) {
                return;
            }

            const limites = modalRestaurar.getBoundingClientRect();

            const fora =
                evento.clientX < limites.left ||
                evento.clientX > limites.right ||
                evento.clientY < limites.top ||
                evento.clientY > limites.bottom;

            if (fora) {
                modalRestaurar.close();
            }

        });


        // Impedir fechamento durante o envio

        modalRestaurar.addEventListener('cancel', evento => {

            if (restaurando) {
                evento.preventDefault();
            }

        });


        // Limpar seleção ao fechar

        modalRestaurar.addEventListener('close', () => {

            formularioRestauracao = null;
            restaurando = false;

            confirmarRestauracao.disabled = false;
            confirmarRestauracao.textContent = 'Sim, restaurar';

            cancelarRestauracao.disabled = false;

        });

    }

});
