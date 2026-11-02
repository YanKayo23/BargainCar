
const campoCidade = document.getElementById('cidade_busca');
const campoCidadeId = document.getElementById('cidade_id');
const resultados = document.getElementById('resultados_cidades');
const campoCpf = document.getElementById('cpf');
const campoSenha = document.getElementById('senha');
const botaoOlho = document.getElementById('alternar-senha');
const formulario = document.querySelector('form');

let controlador = null;

// Pesquisa de cidades

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
            throw new Error('Erro ao buscar cidades');
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
            resultados.textContent = 'Erro ao pesquisar cidades.';
            campoCidade.setAttribute('aria-expanded', 'true');
        }
    }
});

document.addEventListener('click', evento => {
    if (!evento.target.closest('.campo-cidade')) {
        limparResultados();
    }
});

// Máscara visual do CPF

campoCpf.addEventListener('input', () => {
    let numeros = campoCpf.value.replace(/\D/g, '').slice(0, 11);

    numeros = numeros.replace(/^(\d{3})(\d)/, '$1.$2');
    numeros = numeros.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    numeros = numeros.replace(/\.(\d{3})(\d)/, '.$1-$2');

    campoCpf.value = numeros;
});

// Mostrar ou ocultar senha

botaoOlho.addEventListener('click', () => {
    const mostrar = campoSenha.type === 'password';

    campoSenha.type = mostrar ? 'text' : 'password';

    botaoOlho.setAttribute(
        'aria-label',
        mostrar ? 'Ocultar senha' : 'Mostrar senha'
    );

    botaoOlho.setAttribute('aria-pressed', String(mostrar));

    const icone = document.getElementById('icone-olho');

    icone.style.opacity = mostrar ? '0.5' : '1';
});

// Validação e preparação dos dados

formulario.addEventListener('submit', evento => {
    if (!campoCidadeId.value) {
        evento.preventDefault();
        campoCidade.setCustomValidity(
            'Selecione uma cidade da lista.'
        );
        campoCidade.reportValidity();
        return;
    }

    // O banco armazena CPF sem pontos ou traço.
    campoCpf.value = campoCpf.value.replace(/\D/g, '');
});

const campoWhatsapp = document.getElementById('contato_padrao');

function formatarWhatsapp(valor) {
    const numeros = valor.replace(/\D/g, '').slice(0, 11);

    if (numeros.length === 0) return '';
    if (numeros.length <= 2) return `(${numeros}`;
    if (numeros.length <= 6) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
    }
    if (numeros.length <= 10) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 6)}-${numeros.slice(6)}`;
    }

    return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`;
}

campoWhatsapp.addEventListener('input', () => {
    campoWhatsapp.value = formatarWhatsapp(campoWhatsapp.value);
});

campoWhatsapp.value = formatarWhatsapp(campoWhatsapp.value);


const requisitoTamanho = document.getElementById('requisito-tamanho');
const requisitoMaiuscula = document.getElementById('requisito-maiuscula');

function atualizarRequisito(elemento, valido) {
    elemento.classList.toggle('valido', valido);

    const indicador = elemento.querySelector('span');
    indicador.textContent = valido ? '✓' : '○';
}

function verificarSenha() {
    const senha = campoSenha.value;

    const tamanhoValido = senha.length >= 8;
    const maiusculaValida = /[A-Z]/.test(senha);

    atualizarRequisito(requisitoTamanho, tamanhoValido);
    atualizarRequisito(requisitoMaiuscula, maiusculaValida);
}

campoSenha.addEventListener('input', verificarSenha);
verificarSenha();

