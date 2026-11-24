<?php
namespace App\Controllers;

use App\Models\LojaModel;
use App\Models\AnuncioModel;
use App\Models\MidiaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VerPerfilLojaController extends BaseController
{
    public function index(int $id): string
    {
        $loja = (new LojaModel())->select('id, usuario_id, nome_empresa, foto_perfil, foto_capa, data_cadastro')->find($id);
        if (!$loja) {
            throw PageNotFoundException::forPageNotFound('Loja não encontrada ou indisponível.');
        }
        $anuncios = (new AnuncioModel())
            ->select('id, titulo, preco, ano, cor')
            ->where('loja_id', $id)
            ->where('usuario_id', $loja['usuario_id'])
            ->where('tipo_anunciante', 'loja')
            ->whereIn('status', ['ativo', 'pendente'])
            ->where('motivo_indisponibilidade', null)
            ->where('data_expiracao >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')->findAll();
        $fotos = [];
        if ($anuncios) {
            $midias = (new MidiaModel())->select('id, anuncio_id')
                ->whereIn('anuncio_id', array_column($anuncios, 'id'))
                ->where('tipo', 'foto')->orderBy('ordem', 'ASC')->orderBy('id', 'ASC')->findAll();
            foreach ($midias as $midia) {
                $fotos[$midia['anuncio_id']] ??= $midia['id'];
            }
        }
        unset($loja['usuario_id']);
        $this->response->setHeader('Cache-Control', 'private, no-store');
        return view('lojas/VerPerfilLoja', compact('loja', 'anuncios', 'fotos'));
    }
}
