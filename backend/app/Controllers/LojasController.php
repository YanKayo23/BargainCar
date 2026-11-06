<?php

namespace App\Controllers;

use App\Models\LojaModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Throwable;

class LojasController extends BaseController
{
    public function criar(): string|RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // O soft delete ignora lojas arquivadas
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($lojaAtiva) {
            return redirect()->to('/')
                ->with(
                    'erro',
                    'Você já possui uma loja ativa. '
                        . 'Para criar outra, primeiro desative a atual.'
                );
        }

        return view('lojas/CriarLoja');
    }

    public function salvar(): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // Impede mais de uma loja ativa
        if ($lojaModel->where('usuario_id', $usuarioId)->first()) {
            return redirect()->to('/')
                ->with(
                    'erro',
                    'Você já possui uma loja ativa.'
                );
        }

        // Recebe e normaliza os dados
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

        // Regras de validação
        $regras = [
            'nome_empresa' => [
                'label' => 'Nome da empresa',
                'rules' => 'required|max_length[150]',
                'errors' => [
                    'required' => 'Informe o nome da empresa.',
                    'max_length' => 'O nome da empresa deve ter no máximo 150 caracteres.',
                ],
            ],

            'cidade_id' => [
                'label' => 'Cidade',
                'rules' => 'required|is_not_unique[cidades.codigo_ibge]',
                'errors' => [
                    'required' => 'Selecione uma cidade da lista.',
                    'is_not_unique' => 'A cidade selecionada não foi encontrada.',
                ],
            ],

            'contato' => [
                'label' => 'Contato da loja',
                'rules' => 'required|max_length[30]',
                'errors' => [
                    'required' => 'Informe o contato da loja.',
                    'max_length' => 'O contato deve ter no máximo 30 caracteres.',
                ],
            ],

            'cnpj' => [
                'label' => 'CNPJ',
                'rules' => 'permit_empty|exact_length[14]|cnpjValido|is_unique[lojas.cnpj]',
                'errors' => [
                    'exact_length' => 'O CNPJ deve conter exatamente 14 dígitos.',
                    'cnpjValido' => 'O CNPJ informado é inválido.',
                    'is_unique' => 'Este CNPJ já está cadastrado em outra loja.',
                ],
            ],
        ];

        if (! $this->validateData($dados, $regras)) {
            return redirect()->back()
                ->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $dadosValidados = $this->validator->getValidated();

        // O responsável é identificado pela sessão
        $dadosValidados['usuario_id'] = $usuarioId;
        $dadosValidados['cnpj'] = $dados['cnpj'];

        try {
            $resultado = $lojaModel->insert($dadosValidados);

            if ($resultado === false) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'erros',
                        $lojaModel->errors() ?: [
                            'geral' => 'Não foi possível cadastrar a loja.',
                        ]
                    );
            }
        } catch (DatabaseException $e) {
            // Trata possíveis duplicidades sem mostrar SQL ao usuário
            log_message('error', 'Erro ao cadastrar loja: {erro}', [
                'erro' => $e->getMessage(),
            ]);

            // Verifica se outra requisição criou uma loja ativa
            if (
                $lojaModel->where('usuario_id', $usuarioId)->first()
            ) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'erro',
                        'Você já possui uma loja ativa.'
                    );
            }

            // Verifica se o CNPJ passou a estar em uso
            if (
                $dados['cnpj'] !== null
                && $lojaModel->withDeleted()
                ->where('cnpj', $dados['cnpj'])
                ->first()
            ) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'erro',
                        'Este CNPJ já está cadastrado em outra loja.'
                    );
            }

            return redirect()->back()
                ->withInput()
                ->with(
                    'erro',
                    'Não foi possível cadastrar a loja. Tente novamente.'
                );
        }

        return redirect()->to('/')
            ->with('sucesso', 'Loja cadastrada com sucesso!');
    }


    // Desativa uma loja sem apagar seus dados
    public function desativar(int $id): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // Busca apenas uma loja ativa do usuário autenticado
        $loja = $lojaModel
            ->where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (! $loja) {
            return redirect()->to('/')
                ->with('erro', 'Loja ativa não encontrada ou você não tem permissão para desativá-la.');
        }

        // Soft delete: preenche deleted_at
        if ($lojaModel->delete($id) === false) {
            return redirect()->to('/')
                ->with('erro', 'Não foi possível desativar a loja.');
        }

        return redirect()->to('/')
            ->with('sucesso', 'Loja desativada com sucesso!');
    }

    // Reativa uma loja anteriormente desativada
    public function reativar(int $id): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $lojaModel = new LojaModel();

        // Verifica se já existe outra loja ativa
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($lojaAtiva) {
            return redirect()->to('/')
                ->with('erro', 'Desative sua loja atual antes de reativar outra.');
        }

        // Busca a loja desativada pertencente ao usuário
        $loja = $lojaModel
            ->onlyDeleted()
            ->where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (! $loja) {
            return redirect()->to('/')
                ->with('erro', 'Loja desativada não encontrada ou você não tem permissão para reativá-la.');
        }

        // Atualiza deleted_at diretamente para NULL
        // O Model continua configurado para soft delete
        $db = db_connect();

        try {
            $db->table('lojas')
                ->where('id', $id)
                ->where('usuario_id', $usuarioId)
                ->where('deleted_at IS NOT NULL', null, false)
                ->update(['deleted_at' => null]);
        } catch (\Throwable $e) {
            log_message('error', 'Falha ao reativar loja: {erro}', [
                'erro' => $e->getMessage(),
            ]);

            return redirect()->to('/')
                ->with('erro', 'Não foi possível reativar a loja. Verifique se você já possui outra loja ativa.');
        }

        return redirect()->to('/')
            ->with('sucesso', 'Loja reativada com sucesso! Você já pode publicar novos anúncios.');
    }
}
