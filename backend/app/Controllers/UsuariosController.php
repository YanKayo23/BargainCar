<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class UsuariosController extends BaseController
{
    public function cadastro(): string
    {
        return view('usuarios/cadastro');
    }

    public function salvar(): RedirectResponse
    {
        $regras = [
            'nome' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email]',
            'cpf' => 'required|regex_match[/^[0-9]{11}$/]|cpf_valido|is_unique[usuarios.cpf]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato_padrao' => 'permit_empty|max_length[30]',
            'senha' => 'required|min_length[8]|regex_match[/[A-Z]/]',
            'foto_perfil' => 'permit_empty|is_image[foto_perfil]|mime_in[foto_perfil,image/jpeg,image/png,image/webp]|max_size[foto_perfil,2048]',
        ];


        $mensagens = [
            'nome' => [
                'required' => 'Informe seu nome completo.',
            ],
            'email' => [
                'required' => 'Informe seu e-mail.',
                'valid_email' => 'Digite um e-mail válido.',
                'is_unique' => 'Este e-mail já está cadastrado.',
            ],
            'cpf' => [
                'required' => 'Informe seu CPF.',
                'regex_match' => 'O CPF deve conter 11 números.',
                'cpf_valido' => 'O CPF informado é inválido.',
                'is_unique' => 'Este CPF já está cadastrado.',
            ],
            'cidade_id' => [
                'required' => 'Selecione sua cidade.',
                'is_not_unique' => 'Selecione uma cidade válida.',
            ],
            'foto_perfil' => [
                'is_image' => 'Selecione um arquivo de imagem válido.',
                'mime_in' => 'A foto deve ser JPG, PNG ou WebP.',
                'max_size' => 'A foto deve ter no máximo 2 MB.',
            ],
            'senha' => [
                'required' => 'Crie uma senha.',
                'min_length' => 'A senha deve ter pelo menos 8 caracteres.',
                'regex_match' => 'A senha precisa ter pelo menos uma letra maiúscula.',
            ],
        ];


        $dadosRecebidos = $this->request->getPost();
        $dadosRecebidos['foto_perfil'] = $this->request->getFile('foto_perfil');

        $dadosRecebidos['cpf'] = preg_replace(
            '/\D/',
            '',
            (string) ($dadosRecebidos['cpf'] ?? '')


        );

        if (! $this->validateData($dadosRecebidos, $regras, $mensagens)) {
            return redirect()->back()
                ->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $dadosValidados = $this->validator->getValidated();

        $usuarioModel = new UsuarioModel();

        $dados = [
            'nome' => $dadosValidados['nome'],
            'email' => $dadosValidados['email'],
            'cpf' => $dadosValidados['cpf'],
            'cidade_id' => $dadosValidados['cidade_id'],
            'contato_padrao' => $dadosValidados['contato_padrao'] ?? null,
            'senha_hash' => password_hash(
                $dadosValidados['senha'],
                PASSWORD_DEFAULT
            ),
        ];

        // Salva a foto somente depois da validação dos demais campos.
        $foto = $this->request->getFile('foto_perfil');
        $caminhoFoto = null;

        if ($foto !== null && $foto->isValid() && ! $foto->hasMoved()) {
            $pasta = FCPATH . 'uploads/usuarios';
            if (! is_dir($pasta) && ! mkdir($pasta, 0755, true) && ! is_dir($pasta)) {
                return redirect()->back()->withInput()->with('erros', [
                    'foto_perfil' => 'Não foi possível preparar a pasta de imagens.',
                ]);
            }

            // A extensão é definida pelo MIME detectado, nunca pelo nome enviado.
            $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $tipoDetectado = $foto->getMimeType();
            if (! isset($extensoes[$tipoDetectado])) {
                return redirect()->back()->withInput()->with('erros', [
                    'foto_perfil' => 'O formato da imagem não é permitido.',
                ]);
            }
            $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$tipoDetectado];
            try {
                $foto->move($pasta, $nomeArquivo);
            } catch (\Throwable $e) {
                log_message('error', 'Falha ao salvar foto de cadastro: {erro}', ['erro' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('erros', [
                    'foto_perfil' => 'Não foi possível enviar a foto. Tente novamente.',
                ]);
            }
            $caminhoFoto = 'uploads/usuarios/' . $nomeArquivo;
            $dados['foto_perfil'] = $caminhoFoto;
        }

        try {
            $inserido = $usuarioModel->insert($dados);
        } catch (\Throwable $e) {
            log_message('error', 'Falha no cadastro de usuário: {erro}', ['erro' => $e->getMessage()]);
            $inserido = false;
        }

        if ($inserido === false) {
            if ($caminhoFoto !== null && is_file(FCPATH . $caminhoFoto)) {
                unlink(FCPATH . $caminhoFoto);
            }
            return redirect()->back()
                ->withInput()
                ->with('erros', [
                    'cadastro' => 'Não foi possível cadastrar o usuário.',
                ]);
        }

        // Autentica o usuário recém-cadastrado e evita reutilizar a sessão anterior.
        session()->regenerate(true);
        session()->set([
            'usuario_id'   => (int) $inserido,
            'usuario_nome' => $dados['nome'],
            'logado'       => true,
        ]);

        return redirect()->to(site_url('/'))
            ->with('sucesso', 'Usuário cadastrado com sucesso!');
    }

}
