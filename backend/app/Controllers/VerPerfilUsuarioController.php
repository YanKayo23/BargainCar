<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\AnuncioModel;
use App\Models\MidiaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VerPerfilUsuarioController extends BaseController
{
    public function index(int $id): string
    {
        $usuario = (new UsuarioModel())
            ->select('id, nome, foto_perfil, data_cadastro')->find($id);
        if (!$usuario) {
            throw PageNotFoundException::forPageNotFound('Usuário não encontrado.');
        }

        $anuncios = (new AnuncioModel())
            ->select('id, titulo, preco, ano, cor')
            ->where('usuario_id', $id)
            ->where('tipo_anunciante', 'pessoa')
            ->where('loja_id', null)
            ->whereIn('status', ['ativo', 'pendente'])
            ->where('motivo_indisponibilidade', null)
            ->where('data_expiracao >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')->findAll();

        $fotos = [];
        if ($anuncios) {
            $midias = (new MidiaModel())->select('id, anuncio_id')
                ->whereIn('anuncio_id', array_column($anuncios, 'id'))
                ->where('tipo', 'foto')->orderBy('ordem', 'ASC')
                ->orderBy('id', 'ASC')->findAll();
            foreach ($midias as $midia) {
                $fotos[$midia['anuncio_id']] ??= $midia['id'];
            }
        }
        $this->response->setHeader('Cache-Control', 'private, no-store');
        return view('usuarios/VerPerfilUsuario', compact('usuario', 'anuncios', 'fotos'));
    }
}
