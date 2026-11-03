<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    // Exibe a página de login
    public function login(): string
    {
        return view('auth/login');
    }

    // Recebe os dados do formulário
    public function autenticar(): RedirectResponse
    {
        $cpf = preg_replace(
            '/\D/',
            '',
            (string) $this->request->getPost('cpf')
        );

        $senha = (string) $this->request->getPost('senha');

        // Verifica se os campos foram preenchidos
        if (strlen($cpf) !== 11 || $senha === '') {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'Informe um CPF válido e sua senha.');
        }

        // Consulta o usuário no MariaDB
        $usuarioModel = new UsuarioModel();

        $usuario = $usuarioModel
            ->where('cpf', $cpf)
            ->first();

        // Verifica a senha armazenada como hash
        if (
            ! $usuario ||
            ! password_verify($senha, $usuario['senha_hash'])
        ) {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'CPF ou senha incorretos.');
        }

        // Renova a sessão por segurança
        session()->regenerate(true);

        // Armazena os dados necessários na sessão
        session()->set([
            'usuario_id' => $usuario['id'],
            'usuario_nome' => $usuario['nome'],
            'logado' => true,
        ]);

        // Redireciona após o login
        return redirect()->to('/')
            ->with('sucesso', 'Login realizado com sucesso!');
    }
}
