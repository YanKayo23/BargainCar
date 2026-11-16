
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

    formulariosReativacao.forEach((formulario) => {

        formulario.addEventListener('submit', (evento) => {

            const nomeLoja = formulario.dataset.nomeLoja || 'esta loja';

            const confirmado = confirm(
                `Deseja reativar ${nomeLoja}?\n\n` +
                'Os anúncios antigos permanecerão arquivados.'
            );

            if (!confirmado) {
                evento.preventDefault();
            }
        });
    });


    // ======================================
    // 2. CONFIRMAÇÃO DE EXCLUSÃO DE ANÚNCIO
    // ======================================

    const formulariosExclusao = document.querySelectorAll(
        '.perfil-form-excluir'
    );

    formulariosExclusao.forEach((formulario) => {

        formulario.addEventListener('submit', (evento) => {

            const titulo = formulario.dataset.titulo || 'este anúncio';

            const confirmado = confirm(
                `Deseja excluir o anúncio "${titulo}"?\n\n` +
                'Ele deixará de aparecer publicamente.'
            );

            if (!confirmado) {
                evento.preventDefault();
            }
        });
    });


    // ======================================
    // 3. CONFIRMAÇÃO DE RENOVAÇÃO
    // ======================================

    const formulariosRenovacao = document.querySelectorAll(
        '.perfil-form-renovar'
    );

    formulariosRenovacao.forEach((formulario) => {

        formulario.addEventListener('submit', (evento) => {

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

    mensagens.forEach((mensagem) => {

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

});
