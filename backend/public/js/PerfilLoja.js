
document.addEventListener('DOMContentLoaded', () => {
    // Modal de confirmação da desativação da loja.
    const modalDesativacao = document.getElementById(
        'pl-modal-desativacao'
    );

    const botaoDesativar = document.getElementById(
        'pl-abrir-desativacao'
    );

    const formularioDesativar = document.getElementById(
        'pl-form-desativar'
    );

    // Abre a confirmação de desativação.
    botaoDesativar?.addEventListener('click', () => {
        modalDesativacao?.showModal();
    });

    // Fecha o modal pelos botões de cancelar.
    document.querySelectorAll('[data-fechar]').forEach(botao => {
        botao.addEventListener('click', () => {
            botao.closest('dialog')?.close();
        });
    });

    // Permite fechar clicando fora do conteúdo.
    modalDesativacao?.addEventListener('click', evento => {
        if (evento.target === modalDesativacao) {
            modalDesativacao.close();
        }
    });

    // Evita que o usuário envie a desativação duas vezes.
    formularioDesativar?.addEventListener('submit', evento => {
        const botaoConfirmar = evento.currentTarget.querySelector(
            'button[type="submit"]'
        );

        if (botaoConfirmar) {
            botaoConfirmar.disabled = true;
            botaoConfirmar.textContent = 'Desativando...';
        }
    });
});
