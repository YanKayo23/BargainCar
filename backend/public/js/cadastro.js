
const campoCidade = document.getElementById('cidade_busca');
const campoCidadeId = document.getElementById('cidade_id');
const resultados = document.getElementById('resultados_cidades');

let controlador = null;

campoCidade.addEventListener('input', async () => {
    const termo = campoCidade.value.trim();

    campoCidadeId.value = '';
    resultados.replaceChildren();

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
            throw new Error('Erro ao buscar cidades');
        }

        const cidades = await resposta.json();

        if (sinal.aborted || campoCidade.value.trim() !== termo) {
            return;
        }

        cidades.forEach(cidade => {
            const opcao = document.createElement('div');

            opcao.className = 'cidade-opcao';
            opcao.textContent = `${cidade.nome} - ${cidade.uf}`;

            opcao.addEventListener('click', () => {
                campoCidade.value = `${cidade.nome} - ${cidade.uf}`;
                campoCidadeId.value = cidade.codigo_ibge;
                resultados.replaceChildren();
            });

            resultados.appendChild(opcao);
        });
    } catch (erro) {
        if (erro.name !== 'AbortError') {
            resultados.textContent = 'Erro ao pesquisar cidades.';
        }
    }
});

document.querySelector('form').addEventListener('submit', evento => {
    if (!campoCidadeId.value) {
        evento.preventDefault();
        campoCidade.setCustomValidity('Selecione uma cidade da lista.');
        campoCidade.reportValidity();
    }
});

campoCidade.addEventListener('input', () => {
    campoCidade.setCustomValidity('');
});
