<?php

namespace App\Controllers;

use App\Models\LojaModel;
use CodeIgniter\HTTP\RedirectResponse;

class LojasController extends BaseController
{
    // Exibe o formulário de criação de loja
    public function criar(): string|RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // Procura somente lojas ativas
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($lojaAtiva) {
            return redirect()->to('/')
                ->with('erro', 'Você já possui uma loja ativa.');
        }

        return view('lojas/criar');
    }

    // Recebe e salva os dados da loja
    public function salvar(): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // Impede duas lojas ativas para o mesmo usuário
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($lojaAtiva) {
            return redirect()->to('/')
                ->with('erro', 'Você já possui uma loja ativa.');
        }

        $cnpj = preg_replace(
            '/\D/',
            '',
            (string) $this->request->getPost('cnpj')
        );

        $dados = [
            'nome_empresa' => trim(
                (string) $this->request->getPost('nome_empresa')
            ),
            'cidade_id' => $this->request->getPost('cidade_id'),
            'contato' => trim(
                (string) $this->request->getPost('contato')
            ),
            'cnpj' => $cnpj === '' ? null : $cnpj,
        ];

        $regras = [
            'nome_empresa' => 'required|max_length[150]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato' => 'required|max_length[30]',
            'cnpj' => 'permit_empty|exact_length[14]|is_unique[lojas.cnpj]',
        ];

        if (! $this->validateData($dados, $regras)) {
            return redirect()->back()
                ->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $dadosValidados = $this->validator->getValidated();

        // O ID vem da sessão, nunca do formulário
        $dadosValidados['usuario_id'] = $usuarioId;

        // Mantém o CNPJ vazio como NULL
        $dadosValidados['cnpj'] = $dados['cnpj'];

        if ($lojaModel->insert($dadosValidados) === false) {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'Não foi possível cadastrar a loja.');
        }

        return redirect()->to('/')
            ->with('sucesso', 'Loja cadastrada com sucesso!');
    }
}
