
/* ========================================
   BARGAINCAR - CRIAR LOJA
======================================== */

const campoCidade = document.getElementById('cidade_busca');
const campoCidadeId = document.getElementById('cidade_id');
const resultados = document.getElementById('resultados_cidades');

const campoNome = document.getElementById('nome_empresa');
const campoCnpj = document.getElementById('cnpj');
const campoContato = document.getElementById('contato');

const formulario = document.getElementById('form-loja');

const previaNome = document.getElementById('previa-nome');
const previaCidade = document.getElementById('previa-cidade');
const previaInicial = document.getElementById('previa-inicial');

let controlador = null;

/* ========================================
   AUTOCOMPLETE DE CIDADES
======================================== */

function limparResultados() {
    resultados.replaceChildren();
    campoCidade.setAttribute('aria-expanded', 'false');
}

campoCidade.addEventListener('input', async () => {
    const termo = campoCidade.value.trim();

    campoCidadeId.value = '';
    campoCidade.setCustomValidity('');
    limparResultados();

    previaCidade.textContent = termo || 'Cidade da loja';

    if (controlador) {
        controlador.abort();
    }

    if (termo.length < 2) {
        return;
    }

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

        if (cidades.length === 0) {
            const mensagem = document.createElement('div');
            mensagem.className = 'cidade-mensagem';
            mensagem.textContent = 'Nenhuma cidade encontrada.';
            resultados.appendChild(mensagem);
            campoCidade.setAttribute('aria-expanded', 'true');
            return;
        }

        cidades.forEach(cidade => {
            const opcao = document.createElement('button');

            opcao.type = 'button';
            opcao.className = 'cidade-opcao';
            opcao.setAttribute('role', 'option');
            opcao.textContent = `${cidade.nome} - ${cidade.uf}`;

            opcao.addEventListener('click', () => {
                const nomeCidade = `${cidade.nome} - ${cidade.uf}`;

                campoCidade.value = nomeCidade;
                campoCidadeId.value = cidade.codigo_ibge;

                previaCidade.textContent = nomeCidade;

                campoCidade.setCustomValidity('');
                limparResultados();
            });

            resultados.appendChild(opcao);
        });

        campoCidade.setAttribute('aria-expanded', 'true');

    } catch (erro) {
        if (erro.name !== 'AbortError') {
            const mensagem = document.createElement('div');
            mensagem.className = 'cidade-mensagem';
            mensagem.textContent = 'Erro ao pesquisar cidades.';

            resultados.replaceChildren(mensagem);
            campoCidade.setAttribute('aria-expanded', 'true');
        }
    }
});

document.addEventListener('click', evento => {
    if (!evento.target.closest('.campo-cidade')) {
        limparResultados();
    }
});

/* ========================================
   PRÉ-VISUALIZAÇÃO DA LOJA
======================================== */

function atualizarPreviaNome() {
    const nome = campoNome.value.trim();

    previaNome.textContent = nome || 'Nome da empresa';

    previaInicial.textContent = nome
        ? nome.charAt(0).toUpperCase()
        : 'L';
}

campoNome.addEventListener('input', atualizarPreviaNome);

/* ========================================
   MÁSCARA DO CNPJ
======================================== */

function formatarCnpj(valor) {
    let numeros = valor.replace(/\D/g, '').slice(0, 14);

    numeros = numeros.replace(/^(\d{2})(\d)/, '$1.$2');
    numeros = numeros.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    numeros = numeros.replace(/\.(\d{3})(\d)/, '.$1/$2');
    numeros = numeros.replace(/(\d{4})(\d)/, '$1-$2');

    return numeros;
}

campoCnpj.addEventListener('input', () => {
    campoCnpj.value = formatarCnpj(campoCnpj.value);
});

/* ========================================
   MÁSCARA DO CONTATO
======================================== */

function formatarContato(valor) {
    const numeros = valor.replace(/\D/g, '').slice(0, 11);

    if (numeros.length === 0) {
        return '';
    }

    if (numeros.length <= 2) {
        return `(${numeros}`;
    }

    if (numeros.length <= 6) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
    }

    if (numeros.length <= 10) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 6)}-${numeros.slice(6)}`;
    }

    return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`;
}

campoContato.addEventListener('input', () => {
    campoContato.value = formatarContato(campoContato.value);
});

/* ========================================
   VALIDAÇÃO ANTES DO ENVIO
======================================== */

formulario.addEventListener('submit', evento => {
    if (!campoCidadeId.value) {
        evento.preventDefault();

        campoCidade.setCustomValidity(
            'Selecione uma cidade da lista.'
        );

        campoCidade.reportValidity();
        return;
    }

    // Envia o CNPJ somente com números
    campoCnpj.value = campoCnpj.value.replace(/\D/g, '');
});

/* ========================================
   RESTAURAÇÃO DOS CAMPOS
======================================== */

atualizarPreviaNome();

previaCidade.textContent =
    campoCidade.value.trim() || 'Cidade da loja';

campoCnpj.value = formatarCnpj(campoCnpj.value);
campoContato.value = formatarContato(campoContato.value);
