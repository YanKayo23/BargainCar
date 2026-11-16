<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\LojaModel;
use App\Models\AnuncioModel;
use CodeIgniter\HTTP\RedirectResponse;

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
                'id'   => $usuario['id'],
                'nome' => $usuario['nome'],
            ],
            'tempoCadastro' => $tempoCadastro,
            'totalAnunciosAtivos' => $totalAnunciosAtivos,
            'lojaAtiva' => $lojaAtiva,
            'lojasDesativadas' => $lojasDesativadas,
            'anunciosPessoais' => $anunciosPessoais,
        ];

        return view('usuarios/MeuPerfil', $dados);
    }
}
