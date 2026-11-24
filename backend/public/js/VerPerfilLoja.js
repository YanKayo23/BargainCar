document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // ORDENAÇÃO DOS ANÚNCIOS DA LOJA
    // ==========================================

    const seletorOrdem = document.getElementById(
        'vp-ordem'
    );

    const gridAnuncios = document.getElementById(
        'vp-grid'
    );

    if (!seletorOrdem || !gridAnuncios) {
        return;
    }

    const anuncios = Array.from(
        gridAnuncios.querySelectorAll('.vp-card')
    );

    function ordenarAnuncios() {

        const ordem = seletorOrdem.value;

        const anunciosOrdenados = [...anuncios];

        anunciosOrdenados.sort((a, b) => {

            const precoA = Number(a.dataset.preco);
            const precoB = Number(b.dataset.preco);

            const idA = Number(a.dataset.id);
            const idB = Number(b.dataset.id);

            switch (ordem) {

                case 'menor':
                    return precoA - precoB || idB - idA;

                case 'maior':
                    return precoB - precoA || idB - idA;

                case 'recentes':
                default:
                    return idB - idA;
            }

        });

        anunciosOrdenados.forEach(anuncio => {
            gridAnuncios.appendChild(anuncio);
        });
    }

    seletorOrdem.addEventListener(
        'change',
        ordenarAnuncios
    );

    ordenarAnuncios();

});
