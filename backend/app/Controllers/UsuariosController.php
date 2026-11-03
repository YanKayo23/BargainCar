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
            'senha' => 'required|min_length[8]|regex_match[/[A-Z]/]',
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
                'is_unique' => 'Este CPF já está cadastrado.',
            ],
            'cidade_id' => [
                'required' => 'Selecione sua cidade.',
                'is_not_unique' => 'Selecione uma cidade válida.',
            ],
            'senha' => [
                'required' => 'Crie uma senha.',
                'min_length' => 'A senha deve ter pelo menos 8 caracteres.',
                'regex_match' => 'A senha precisa ter pelo menos uma letra maiúscula.',
            ],
        ];


        $dadosRecebidos = $this->request->getPost();

        $dadosRecebidos['cpf'] = preg_replace(
            '/\D/',
            '',
            (string) ($dadosRecebidos['cpf'] ?? '')


        );

        if (! $this->cpfValido($dadosRecebidos['cpf'])) {
            return redirect()->back()
                ->withInput()
                ->with('erros', [
                    'cpf' => 'O CPF informado é inválido.',
                ]);
        }

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


    private function cpfValido(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($tamanho = 9; $tamanho < 11; $tamanho++) {
            $soma = 0;

            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $cpf[$i] * ($tamanho + 1 - $i);
            }

            $resto = ($soma * 10) % 11;
            $digito = $resto === 10 ? 0 : $resto;

            if ($digito !== (int) $cpf[$tamanho]) {
                return false;
            }
        }

        return true;
    }
}
