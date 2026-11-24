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

        session()->set('modo_perfil', 'pessoa');

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
        $anunciosPessoais = $anuncioModel->withDeleted()
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


        $historico = [];
        $atuais = [];
        foreach ($anunciosPessoais as $a) {
            $situacao = \App\Services\AnuncioCicloVidaService::situacao($a);
            if ($situacao === null) $atuais[] = $a;
            else { $a['situacao_historico'] = $situacao; $a['foto_id'] = $a['primeira_foto']['id'] ?? null; $historico[] = $a; }
        }
        $anunciosPessoais = $atuais;
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
            'historico' => $historico,
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

        // A foto é alterada somente após validar os dados pessoais.
        $foto = $this->request->getFile('foto_perfil');
        $remover = $this->request->getPost('remover_foto') === '1';
        $novaFoto = null;
        $fotoAntiga = $usuario['foto_perfil'] ?? null;
        $dados['contato_padrao'] = $dados['contato_padrao'] ?: null;

        if ($foto !== null && $foto->getError() !== UPLOAD_ERR_NO_FILE) {
            $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (! $foto->isValid() || $foto->hasMoved() || $foto->getSize() > 2097152
                || ! isset($extensoes[$foto->getMimeType()])
                || ! @getimagesize($foto->getTempName())) {
                return redirect()->back()->withInput()->with('erros', [
                    'foto_perfil' => 'Selecione uma imagem JPG, PNG ou WebP válida de até 2 MB.',
                ]);
            }
            try {
                $pasta = FCPATH . 'uploads/usuarios';
                if (! is_dir($pasta) && ! mkdir($pasta, 0755, true) && ! is_dir($pasta)) {
                    throw new \RuntimeException('Pasta de imagens indisponível.');
                }
                $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$foto->getMimeType()];
                $foto->move($pasta, $nomeArquivo);
                $novaFoto = 'uploads/usuarios/' . $nomeArquivo;
                $dados['foto_perfil'] = $novaFoto;
            } catch (\Throwable $e) {
                log_message('error', 'Falha no upload: {erro}', ['erro' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('erros', [
                    'foto_perfil' => 'Não foi possível enviar a foto.',
                ]);
            }
        } elseif ($remover) {
            $dados['foto_perfil'] = null;
        }

        try {
            $salvou = $usuarioModel->update($usuarioId, $dados);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao editar perfil: {erro}', ['erro' => $e->getMessage()]);
            $salvou = false;
        }

        if (! $salvou) {
            $this->excluirFotoPerfil($novaFoto);
            return redirect()->back()->withInput()->with('erros', [
                'geral' => 'Não foi possível salvar as alterações. Tente novamente.',
            ]);
        }

        if (array_key_exists('foto_perfil', $dados) && $fotoAntiga !== $dados['foto_perfil']) {
            $this->excluirFotoPerfil($fotoAntiga);
        }

        session()->set('usuario_nome', $dados['nome']);

        return redirect()->to(site_url('meu-perfil'))
            ->with('sucesso', 'Dados do perfil atualizados com sucesso!');
    }

    private function excluirFotoPerfil(?string $caminho): void
    {
        if (! $caminho || ! preg_match('~^uploads/usuarios/[a-f0-9]{32}\.(jpg|png|webp)$~D', $caminho)) {
            return;
        }
        $arquivo = FCPATH . $caminho;
        if (is_file($arquivo) && ! @unlink($arquivo)) {
            log_message('error', 'Não foi possível excluir a foto antiga.');
        }
    }
}
