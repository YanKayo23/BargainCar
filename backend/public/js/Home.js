
/* BargainCar — Home: seleção de cidade de referência */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('home-form-cidade');
    if (!form) return;

    const campo = document.getElementById('home-cidade-busca');
    const codigo = document.getElementById('home-cidade-id');
    const sugestoes = document.getElementById('home-cidade-sugestoes');

    let espera;
    let controlador;
    let sequencia = 0;

    function fechar() {
        sugestoes.replaceChildren();
        sugestoes.hidden = true;
    }

    function aviso(texto) {
        sugestoes.replaceChildren();

        const p = document.createElement('p');
        p.textContent = texto;

        sugestoes.appendChild(p);
        sugestoes.hidden = false;
    }

    async function consultar(termo) {
        if (controlador) controlador.abort();

        controlador = new AbortController();
        const consultaAtual = ++sequencia;

        try {
            const url = new URL(
                form.dataset.urlCidades,
                window.location.origin
            );

            url.searchParams.set('busca', termo);

            const resposta = await fetch(url.toString(), {
                signal: controlador.signal,
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error('Falha na consulta');
            }

            const cidades = await resposta.json();

            if (consultaAtual !== sequencia) return;

            sugestoes.replaceChildren();

            if (!Array.isArray(cidades) || cidades.length === 0) {
                aviso('Nenhuma cidade encontrada.');
                return;
            }

            cidades.forEach(cidade => {
                const botao = document.createElement('button');

                botao.type = 'button';
                botao.setAttribute('role', 'option');

                botao.textContent = `${cidade.nome} - ${cidade.uf}`;

                botao.addEventListener('click', () => {
                    clearTimeout(espera);

                    if (controlador) controlador.abort();

                    sequencia++;

                    campo.value = `${cidade.nome} - ${cidade.uf}`;
                    codigo.value = String(cidade.codigo_ibge);

                    fechar();
                });

                sugestoes.appendChild(botao);
            });

            sugestoes.hidden = false;

        } catch (erro) {
            if (
                erro.name === 'AbortError' ||
                consultaAtual !== sequencia
            ) {
                return;
            }

            aviso('Erro ao buscar cidades. Tente novamente.');
        }
    }

    // Busca cidades conforme o visitante digita.
    campo.addEventListener('input', () => {
        clearTimeout(espera);

        if (controlador) controlador.abort();

        sequencia++;
        codigo.value = '';

        fechar();

        const termo = campo.value.trim();

        if (termo.length >= 2) {
            espera = setTimeout(() => consultar(termo), 300);
        }
    });

    // Fecha as sugestões ao pressionar ESC.
    campo.addEventListener('keydown', e => {
        if (e.key === 'Escape') fechar();
    });

    // Fecha as sugestões ao clicar fora do campo.
    document.addEventListener('click', e => {
        if (!e.target.closest('.home-cidade-campo')) {
            fechar();
        }
    });

    // Impede o envio sem selecionar uma cidade válida.
    form.addEventListener('submit', e => {
        if (!codigo.value) {
            e.preventDefault();

            alert('Selecione uma cidade na lista de sugestões.');
            campo.focus();
        }
    });
});
