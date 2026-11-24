/* ==========================================
   BARGAINCAR - MEU PERFIL
========================================== */

document.addEventListener('DOMContentLoaded', () => {

    // ======================================
    // 1. CONFIRMAÇÃO DE REATIVAÇÃO DE LOJA
    // ======================================

    const formulariosReativacao = document.querySelectorAll(
        '.perfil-form-reativar'
    );

    formulariosReativacao.forEach(formulario => {

        formulario.addEventListener('submit', evento => {

            const nomeLoja = formulario.dataset.nomeLoja || 'esta loja';

            const confirmado = confirm(
                `Deseja reativar ${nomeLoja}?\n\n` +
                'Os anúncios antigos permanecerão no histórico.'
            );

            if (!confirmado) {
                evento.preventDefault();
            }

        });

    });


    // ======================================
    // 2. CONFIRMAÇÃO DE EXCLUSÃO DE ANÚNCIO
    // ======================================

    const modalExcluir = document.getElementById(
        'modal-excluir-anuncio'
    );

    const textoExcluir = document.getElementById(
        'modal-excluir-texto'
    );

    const botaoCancelar = document.getElementById(
        'modal-excluir-cancelar'
    );

    const botaoConfirmar = document.getElementById(
        'modal-excluir-confirmar'
    );

    let formularioSelecionado = null;

    if (
        modalExcluir &&
        textoExcluir &&
        botaoCancelar &&
        botaoConfirmar
    ) {

        document.querySelectorAll('.perfil-form-excluir')
            .forEach(formulario => {

                formulario.addEventListener('submit', evento => {

                    evento.preventDefault();

                    formularioSelecionado = formulario;

                    const titulo =
                        formulario.dataset.tituloAnuncio || 'este anúncio';

                    textoExcluir.textContent =
                        `Tem certeza de que deseja excluir o anúncio "${titulo}"?`;

                    modalExcluir.showModal();

                });

            });

        botaoCancelar.addEventListener('click', () => {
            modalExcluir.close();
        });

        botaoConfirmar.addEventListener('click', () => {

            if (formularioSelecionado) {

                botaoConfirmar.disabled = true;

                formularioSelecionado.submit();

            }

        });

        modalExcluir.addEventListener('close', () => {

            formularioSelecionado = null;

            botaoConfirmar.disabled = false;

        });

    }


    // ======================================
    // 3. CONFIRMAÇÃO DE RENOVAÇÃO
    // ======================================

    const formulariosRenovacao = document.querySelectorAll(
        '.perfil-form-renovar'
    );

    formulariosRenovacao.forEach(formulario => {

        formulario.addEventListener('submit', evento => {

            const confirmado = confirm(
                'Deseja solicitar a renovação deste anúncio?'
            );

            if (!confirmado) {
                evento.preventDefault();
            }

        });

    });


    // ======================================
    // 4. MENSAGENS TEMPORÁRIAS
    // ======================================

    const mensagens = document.querySelectorAll(
        '.perfil-mensagem'
    );

    mensagens.forEach(mensagem => {

        const botaoFechar = mensagem.querySelector(
            '.perfil-mensagem-fechar'
        );

        if (botaoFechar) {

            botaoFechar.addEventListener('click', () => {
                mensagem.remove();
            });

        }

    });


    // ======================================
    // 5. HISTÓRICO DE LOJAS
    // ======================================

    const historico = document.querySelector(
        '.perfil-lojas-historico'
    );

    if (historico) {

        historico.addEventListener('toggle', () => {

            const resumo = historico.querySelector('summary');

            if (resumo) {

                resumo.setAttribute(
                    'aria-expanded',
                    String(historico.open)
                );

            }

        });

    }


    // ======================================
    // 6. MODAL DE RESTAURAÇÃO DE ANÚNCIOS
    // ======================================

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
