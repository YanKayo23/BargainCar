
const campoCidade = document.getElementById('cidade_busca');
const campoCidadeId = document.getElementById('cidade_id');
const resultados = document.getElementById('resultados_cidades');
const formulario = document.querySelector('form');

let controlador = null;

function limparResultados() {
    resultados.replaceChildren();
    campoCidade.setAttribute('aria-expanded', 'false');
}

campoCidade.addEventListener('input', async () => {
    const termo = campoCidade.value.trim();

    campoCidadeId.value = '';
    campoCidade.setCustomValidity('');
    limparResultados();

    if (controlador) controlador.abort();

    if (termo.length < 2) return;

    controlador = new AbortController();
    const sinal = controlador.signal;

    try {
        const resposta = await fetch(
            '/cidades?busca=' + encodeURIComponent(termo),
            { signal: sinal }
        );

        if (!resposta.ok) {
            throw new Error('Falha na pesquisa');
        }

        const cidades = await resposta.json();

        if (sinal.aborted || campoCidade.value.trim() !== termo) {
            return;
        }

        cidades.forEach(cidade => {
            const opcao = document.createElement('button');
            opcao.type = 'button';
            opcao.className = 'cidade-opcao';
            opcao.setAttribute('role', 'option');
            opcao.textContent = `${cidade.nome} - ${cidade.uf}`;

            opcao.addEventListener('click', () => {
                campoCidade.value = `${cidade.nome} - ${cidade.uf}`;
                campoCidadeId.value = cidade.codigo_ibge;
                campoCidade.setCustomValidity('');
                limparResultados();
            });

            resultados.appendChild(opcao);
        });

        campoCidade.setAttribute(
            'aria-expanded',
            cidades.length > 0 ? 'true' : 'false'
        );
    } catch (erro) {
        if (erro.name !== 'AbortError') {
            resultados.textContent = 'Erro ao buscar cidades.';
            campoCidade.setAttribute('aria-expanded', 'true');
        }
    }
});

document.addEventListener('click', evento => {
    if (!evento.target.closest('.campo-cidade')) {
        limparResultados();
    }
});

formulario.addEventListener('submit', evento => {
    if (!campoCidadeId.value) {
        evento.preventDefault();
        campoCidade.setCustomValidity(
            'Selecione uma cidade da lista.'
        );
        campoCidade.reportValidity();
    }
});
