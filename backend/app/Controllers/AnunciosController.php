<?php

namespace App\Controllers;

use App\Models\AnuncioModel;
use App\Models\LojaModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class AnunciosController extends BaseController
{
    // Exibe a página de criação de anúncios
    public function criar(): string|RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $usuarioModel = new UsuarioModel();
        $lojaModel = new LojaModel();

        $usuario = $usuarioModel->find($usuarioId);

        if (! $usuario) {
            return redirect()->to('/login');
        }

        $loja = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        return view('anuncios/CriarAnuncio', [
            'usuario' => $usuario,
            'loja' => $loja,
        ]);
    }

    // Recebe e valida os dados do anúncio
    public function salvar(): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $usuarioModel = new UsuarioModel();
        $lojaModel = new LojaModel();
        $anuncioModel = new AnuncioModel();

        $usuario = $usuarioModel->find($usuarioId);

        if (! $usuario) {
            return redirect()->to('/login');
        }

        $dados = $this->request->getPost();

        $regras = [
            'tipo_anunciante' => 'required|in_list[pessoa,loja]',
            'titulo' => 'required|max_length[150]',
            'ano' => 'required|integer|greater_than_equal_to[1900]',
            'preco' => 'required|numeric|greater_than[0]',
            'cor' => 'required|max_length[50]',
            'descricao' => 'required',
            'defeitos' => 'permit_empty',
            'debitos_pendentes' => 'permit_empty',
            'situacao_documentacao' => 'required|max_length[100]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato' => 'required|max_length[30]',
        ];

        $mensagens = [
            'tipo_anunciante' => [
                'required' => 'Selecione o tipo de anunciante.',
                'in_list' => 'Tipo de anunciante inválido.',
            ],
            'titulo' => [
                'required' => 'Informe o título do anúncio.',
                'max_length' => 'O título pode ter no máximo 150 caracteres.',
            ],
            'ano' => [
                'required' => 'Informe o ano do veículo.',
                'integer' => 'Informe um ano válido.',
                'greater_than_equal_to' => 'O ano informado é inválido.',
            ],
            'preco' => [
                'required' => 'Informe o preço do veículo.',
                'numeric' => 'Informe um preço válido.',
                'greater_than' => 'O preço deve ser maior que zero.',
            ],
            'cor' => [
                'required' => 'Informe a cor do veículo.',
            ],
            'descricao' => [
                'required' => 'Informe a descrição do veículo.',
            ],
            'situacao_documentacao' => [
                'required' => 'Informe a situação da documentação.',
            ],
            'cidade_id' => [
                'required' => 'Selecione a cidade do anúncio.',
                'is_not_unique' => 'Selecione uma cidade válida.',
            ],
            'contato' => [
                'required' => 'Informe o contato do anúncio.',
            ],
        ];

        if (! $this->validateData($dados, $regras, $mensagens)) {
            return redirect()->back()
                ->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $dadosValidados = $this->validator->getValidated();

        $lojaId = null;

        // Verifica se o usuário pode anunciar como loja
        if ($dadosValidados['tipo_anunciante'] === 'loja') {
            $loja = $lojaModel
                ->where('usuario_id', $usuarioId)
                ->first();

            if (! $loja) {
                return redirect()->back()
                    ->withInput()
                    ->with('erros', [
                        'loja' => 'Você precisa possuir uma loja ativa para anunciar como empresa.',
                    ]);
            }

            $lojaId = $loja['id'];
        }

        // O anúncio permanece ativo por 30 dias
        $dataExpiracao = date(
            'Y-m-d H:i:s',
            strtotime('+30 days')
        );

        $novoAnuncio = [
            'tipo_anunciante' => $dadosValidados['tipo_anunciante'],
            'usuario_id' => $usuarioId,
            'loja_id' => $lojaId,
            'cidade_id' => $dadosValidados['cidade_id'],
            'contato' => $dadosValidados['contato'],
            'data_expiracao' => $dataExpiracao,
            'titulo' => $dadosValidados['titulo'],
            'ano' => $dadosValidados['ano'],
            'preco' => $dadosValidados['preco'],
            'cor' => $dadosValidados['cor'],
            'descricao' => $dadosValidados['descricao'],
            'defeitos' => $dadosValidados['defeitos'] ?? null,
            'debitos_pendentes' => $dadosValidados['debitos_pendentes'] ?? null,
            'situacao_documentacao' => $dadosValidados['situacao_documentacao'],
            'status' => 'ativo',
        ];

        if ($anuncioModel->insert($novoAnuncio) === false) {
            return redirect()->back()
                ->withInput()
                ->with('erros', [
                    'cadastro' => 'Não foi possível criar o anúncio.',
                ]);
        }

        return redirect()->to('/')
            ->with('sucesso', 'Anúncio criado com sucesso!');
    }
}
