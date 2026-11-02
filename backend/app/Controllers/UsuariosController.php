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
            'cpf' => 'required|regex_match[/^[0-9]{11}$/]|is_unique[usuarios.cpf]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato_padrao' => 'permit_empty|max_length[30]',
            'senha' => 'required|min_length[8]',
        ];

        $dadosRecebidos = $this->request->getPost();

        $dadosRecebidos['cpf'] = preg_replace(
            '/\D/',
            '',
            (string) ($dadosRecebidos['cpf'] ?? '')
        );

        if (! $this->validateData($dadosRecebidos, $regras)) {
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

        if ($usuarioModel->insert($dados) === false) {
            return redirect()->back()
                ->withInput()
                ->with('erros', [
                    'cadastro' => 'Não foi possível cadastrar o usuário.',
                ]);
        }

        return redirect()->to('/cadastro')
            ->with('sucesso', 'Usuário cadastrado com sucesso!');
    }
}
