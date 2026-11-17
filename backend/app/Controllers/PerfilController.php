<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\LojaModel;
use App\Models\AnuncioModel;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\MidiaModel;
use App\Models\CidadeModel;

class PerfilController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        // Identifica o usuário autenticado.
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        // Carrega os models.
        $usuarioModel = new UsuarioModel();
        $lojaModel = new LojaModel();
        $anuncioModel = new AnuncioModel();

        // Busca os dados do próprio usuário.
        $usuario = $usuarioModel->find($usuarioId);

        if (! $usuario) {
            session()->destroy();
            return redirect()->to('/login');
        }

        // Converte o código IBGE da cidade em nome e UF.
        $cidadeModel = new CidadeModel();
        $cidade = $cidadeModel
            ->where('codigo_ibge', $usuario['cidade_id'])
            ->first();

        $nomeCidade = $cidade
            ? $cidade['nome'] . ' - ' . $cidade['uf']
            : 'Não informada';

        // Busca a loja ativa.
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        // Busca as lojas desativadas.
        $lojasDesativadas = $lojaModel
            ->onlyDeleted()
            ->where('usuario_id', $usuarioId)
            ->orderBy('id', 'DESC')
            ->findAll();

        // Busca somente anúncios da pessoa física.
        // Os anúncios das lojas ficam separados.
        $anunciosPessoais = $anuncioModel
            ->where('usuario_id', $usuarioId)
            ->where('tipo_anunciante', 'pessoa')
            ->orderBy('id', 'DESC')
            ->findAll();


        // Busca a primeira foto de cada anúncio pessoal.
        $midiaModel = new MidiaModel();

        foreach ($anunciosPessoais as &$anuncio) {
            $primeiraFoto = $midiaModel
                ->where('anuncio_id', $anuncio['id'])
                ->where('tipo', 'foto')
                ->orderBy('ordem', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();

            $anuncio['primeira_foto'] = $primeiraFoto;
        }

        // Encerra a referência utilizada no foreach.
        unset($anuncio);


        // Conta anúncios pessoais ativos.
        $totalAnunciosAtivos = 0;

        foreach ($anunciosPessoais as $anuncio) {
            if (
                $anuncio['status'] === 'ativo'
                && ! empty($anuncio['data_expiracao'])
                && strtotime($anuncio['data_expiracao']) >= time()
            ) {
                $totalAnunciosAtivos++;
            }
        }

        // Calcula há quanto tempo o usuário está cadastrado.
        $tempoCadastro = 'Não informado';

        if (! empty($usuario['data_cadastro'])) {
            $cadastro = new \DateTimeImmutable(
                $usuario['data_cadastro']
            );

            $hoje = new \DateTimeImmutable('now');
            $diferenca = $cadastro->diff($hoje);

            if ($diferenca->y > 0) {
                $tempoCadastro = $diferenca->y
                    . ($diferenca->y === 1 ? ' ano' : ' anos');
            } elseif ($diferenca->m > 0) {
                $tempoCadastro = $diferenca->m
                    . ($diferenca->m === 1 ? ' mês' : ' meses');
            } else {
                $tempoCadastro = $diferenca->days
                    . ($diferenca->days === 1 ? ' dia' : ' dias');
            }
        }

        // Envia apenas os dados necessários para a tela.
        $dados = [

            'usuario' => [
                'id'             => $usuario['id'],
                'nome'           => $usuario['nome'],
                'cpf'            => $usuario['cpf'],
                'email'          => $usuario['email'],
                'cidade_id'      => $usuario['cidade_id'],
                'contato_padrao' => $usuario['contato_padrao'],
                'foto_perfil'    => $usuario['foto_perfil'] ?? null,
            ],

            'nomeCidade' => $nomeCidade,
            'tempoCadastro' => $tempoCadastro,
            'totalAnunciosAtivos' => $totalAnunciosAtivos,
            'lojaAtiva' => $lojaAtiva,
            'lojasDesativadas' => $lojasDesativadas,
            'anunciosPessoais' => $anunciosPessoais,
        ];

        return view('usuarios/MeuPerfil', $dados);
    }

    /** Formulário de edição dos dados do usuário autenticado. */
    public function editar(): string|RedirectResponse
    {
        $usuarioId = (int) session()->get('usuario_id');
        if ($usuarioId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $usuario = (new UsuarioModel())->find($usuarioId);
        if (! $usuario) {
            session()->destroy();
            return redirect()->to(site_url('login'));
        }

        $cidade = (new CidadeModel())
            ->where('codigo_ibge', $usuario['cidade_id'])
            ->first();

        return view('usuarios/EditarPerfil', [
            'usuario' => $usuario,
            'nomeCidade' => $cidade
                ? $cidade['nome'] . ' - ' . $cidade['uf']
                : '',
        ]);
    }

    /** Atualiza somente os dados pessoais autorizados. */
    public function atualizar(): RedirectResponse
    {
        $usuarioId = (int) session()->get('usuario_id');
        if ($usuarioId <= 0) {
            return redirect()->to(site_url('login'));
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($usuarioId);
        if (! $usuario) {
            session()->destroy();
            return redirect()->to(site_url('login'));
        }

        $dados = [
            'nome' => trim((string) $this->request->getPost('nome')),
            'email' => trim((string) $this->request->getPost('email')),
            'cpf' => preg_replace('/\D/', '', (string) $this->request->getPost('cpf')),
            'cidade_id' => trim((string) $this->request->getPost('cidade_id')),
            'contato_padrao' => trim((string) $this->request->getPost('contato_padrao')),
        ];

        // A exceção da unicidade é o próprio registro autenticado.
        $regras = [
            'nome' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email,id,' . $usuarioId . ']',
            'cpf' => 'required|regex_match[/^[0-9]{11}$/]|cpf_valido|is_unique[usuarios.cpf,id,' . $usuarioId . ']',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato_padrao' => 'permit_empty|max_length[30]',
        ];
        $mensagens = [
            'nome' => ['required' => 'Informe seu nome completo.', 'max_length' => 'O nome deve ter no máximo 100 caracteres.'],
            'email' => [
                'required' => 'Informe seu e-mail.',
                'valid_email' => 'Digite um e-mail válido.',
                'max_length' => 'O e-mail deve ter no máximo 150 caracteres.',
                'is_unique' => 'Este e-mail já está cadastrado por outro usuário.',
            ],
            'cpf' => [
                'required' => 'Informe seu CPF.',
                'regex_match' => 'O CPF deve conter 11 números.',
                'cpf_valido' => 'O CPF informado é inválido.',
                'is_unique' => 'Este CPF já está cadastrado por outro usuário.',
            ],
            'cidade_id' => [
                'required' => 'Selecione uma cidade da lista.',
                'is_not_unique' => 'Selecione uma cidade válida.',
            ],
            'contato_padrao' => ['max_length' => 'O contato deve ter no máximo 30 caracteres.'],
        ];

        if (! $this->validateData($dados, $regras, $mensagens)) {
            return redirect()->back()->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        // CPF ou e-mail alterado exige confirmação da senha atual.
        $mudouCpf = $dados['cpf'] !== preg_replace('/\D/', '', (string) $usuario['cpf']);
        $mudouEmail = strcasecmp($dados['email'], (string) $usuario['email']) !== 0;
        if ($mudouCpf || $mudouEmail) {
            $senhaAtual = (string) $this->request->getPost('senha_atual');
            if ($senhaAtual === '' || ! password_verify($senhaAtual, (string) ($usuario['senha_hash'] ?? ''))) {
                return redirect()->back()->withInput()->with('erros', [
                    'senha_atual' => 'Informe sua senha atual corretamente para alterar CPF ou e-mail.',
                ]);
            }
        }

        try {
            $salvou = $usuarioModel->update($usuarioId, [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'cpf' => $dados['cpf'],
                'cidade_id' => $dados['cidade_id'],
                'contato_padrao' => $dados['contato_padrao'] !== '' ? $dados['contato_padrao'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao editar perfil: {erro}', ['erro' => $e->getMessage()]);
            $salvou = false;
        }

        if (! $salvou) {
            return redirect()->back()->withInput()->with('erros', [
                'geral' => 'Não foi possível salvar as alterações. Tente novamente.',
            ]);
        }

        session()->set('usuario_nome', $dados['nome']);

        return redirect()->to(site_url('meu-perfil'))
            ->with('sucesso', 'Dados do perfil atualizados com sucesso!');
    }

}
