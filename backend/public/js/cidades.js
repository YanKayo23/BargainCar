
const input = document.getElementById('busca');
const resultados = document.getElementById('resultados');
const selecionada = document.getElementById('selecionada');

let cidadeSelecionada = null;
let controlador = null;

input.addEventListener('input', async () => {
    const termo = input.value.trim();

    cidadeSelecionada = null;
    selecionada.textContent = '';
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
            throw new Error('Erro na pesquisa');
        }

        const cidades = await resposta.json();

        if (sinal.aborted || input.value.trim() !== termo) return;

        cidades.forEach(cidade => {
            const item = document.createElement('div');
            item.className = 'cidade';
            item.textContent = cidade.nome + ' - ' + cidade.uf;

            item.addEventListener('click', () => {
                cidadeSelecionada = cidade.codigo_ibge;
                input.value = cidade.nome + ' - ' + cidade.uf;
                selecionada.textContent =
                    'Código IBGE selecionado: ' + cidadeSelecionada;
                resultados.replaceChildren();
            });

            resultados.appendChild(item);
        });
    } catch (erro) {
        if (erro.name !== 'AbortError') {
            resultados.textContent = 'Não foi possível buscar cidades.';
        }
    }
});
