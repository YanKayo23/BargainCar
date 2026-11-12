
document.addEventListener('DOMContentLoaded', () => {

    const fotoPrincipal =
        document.getElementById('fotoPrincipal');

    const videoPrincipal =
        document.getElementById('videoPrincipal');

    const miniaturas =
        document.querySelectorAll('.miniatura');

    // Galeria interativa

    function selecionarMidia(botao) {
        const tipo = botao.dataset.tipo;
        const url = botao.dataset.url;

        if (!url) return;

        miniaturas.forEach(item => {
            const selecionado = item === botao;

            item.classList.toggle('ativa', selecionado);
            item.setAttribute(
                'aria-pressed',
                String(selecionado)
            );
        });

        if (videoPrincipal) {
            videoPrincipal.pause();
            videoPrincipal.removeAttribute('src');
            videoPrincipal.load();
            videoPrincipal.hidden = true;
        }

        if (tipo === 'foto' && fotoPrincipal) {
            fotoPrincipal.src = url;
            fotoPrincipal.hidden = false;
        }

        if (tipo === 'video' && videoPrincipal) {
            if (fotoPrincipal) {
                fotoPrincipal.hidden = true;
            }

            videoPrincipal.src = url;
            videoPrincipal.hidden = false;
            videoPrincipal.load();
        }
    }

    miniaturas.forEach(botao => {
        botao.addEventListener('click', () => {
            selecionarMidia(botao);
        });
    });

    // Contato do vendedor

    const botaoContato =
        document.getElementById('mostrarContato');

    const dadosContato =
        document.getElementById('dadosContato');

    if (botaoContato && dadosContato) {
        botaoContato.addEventListener('click', () => {
            const mostrar = dadosContato.hidden;

            dadosContato.hidden = !mostrar;

            botaoContato.setAttribute(
                'aria-expanded',
                String(mostrar)
            );

            botaoContato.textContent = mostrar
                ? 'Ocultar contato'
                : 'Ver contato do vendedor';
        });
    }
});
