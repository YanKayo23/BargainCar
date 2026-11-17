<?php

namespace App\Controllers;

use App\Libraries\DistanciaService;
use App\Models\AnuncioModel;
use App\Models\CidadeModel;
use App\Models\MidiaModel;
use App\Models\UsuarioModel;

class HomeController extends BaseController
{
    private const RAIO_KM = 100;

    public function index(): string
    {
        $cidadeModel = new CidadeModel();
        $cidade = null;
        $erro = null;
        $anuncios = [];
        $cidadeInformada = trim((string) $this->request->getGet('cidade_id'));

        // Uma escolha explícita vale para esta visita à Home.
        if ($cidadeInformada !== '') {
            if (ctype_digit($cidadeInformada)) {
                $cidade = $cidadeModel->find($cidadeInformada);
            }
            if (! $cidade) {
                $erro = 'Selecione uma cidade válida da lista.';
            }
        } else {
            // Para usuários autenticados, usa a cidade padrão cadastrada.
            $usuarioId = session()->get('usuario_id');
            if ($usuarioId) {
                $usuario = (new UsuarioModel())->find($usuarioId);
                if ($usuario && ! empty($usuario['cidade_id'])) {
                    $cidade = $cidadeModel->find($usuario['cidade_id']);
                }
            }
        }

        if ($cidade && ! $erro) {
            $db = db_connect();
            $distanciaService = new DistanciaService();
            $latitude = (float) $cidade['latitude'];
            $longitude = (float) $cidade['longitude'];

            // Usa exatamente a expressão de distância da busca existente.
            $expressao = $distanciaService->expressaoSql('c');
            $cidadesCalculadas = $db->query(
                "SELECT c.codigo_ibge, {$expressao} AS distancia_km FROM cidades c",
                [$latitude, $longitude, $latitude]
            )->getResultArray();

            $codigos = [];
            $distancias = [];
            foreach ($cidadesCalculadas as $item) {
                $distancia = (float) $item['distancia_km'];
                if ($distanciaService->dentroDoRaio($distancia, self::RAIO_KM)) {
                    $codigo = (int) $item['codigo_ibge'];
                    $codigos[] = $codigo;
                    $distancias[$codigo] = $distancia;
                }
            }

            if ($codigos !== []) {
                $anuncios = (new AnuncioModel())
                    ->select('anuncios.id, anuncios.titulo, anuncios.ano, anuncios.preco, anuncios.status, anuncios.cidade_id, anuncios.tipo_anunciante, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf, lojas.nome_empresa AS nome_loja')
                    ->join('cidades', 'cidades.codigo_ibge = anuncios.cidade_id')
                    ->join('lojas', 'lojas.id = anuncios.loja_id', 'left')
                    ->whereIn('anuncios.cidade_id', $codigos)
                    ->whereIn('anuncios.status', ['ativo', 'pendente'])
                    ->where('anuncios.data_expiracao >', date('Y-m-d H:i:s'))
                    ->groupStart()
                        ->where('anuncios.tipo_anunciante', 'pessoa')
                        ->orGroupStart()
                            ->where('anuncios.tipo_anunciante', 'loja')
                            ->where('lojas.deleted_at', null)
                            ->where('lojas.id IS NOT NULL', null, false)
                        ->groupEnd()
                    ->groupEnd()
                    ->orderBy('anuncios.id', 'DESC')
                    ->findAll();

                // Uma única consulta para obter as fotos principais dos anúncios.
                $ids = array_column($anuncios, 'id');
                $fotos = [];
                if ($ids !== []) {
                    $midias = (new MidiaModel())
                        ->select('id, anuncio_id')
                        ->whereIn('anuncio_id', $ids)
                        ->where('tipo', 'foto')
                        ->orderBy('ordem', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->findAll();
                    foreach ($midias as $midia) {
                        $id = (int) $midia['anuncio_id'];
                        if (! isset($fotos[$id])) {
                            $fotos[$id] = (int) $midia['id'];
                        }
                    }
                }

                foreach ($anuncios as &$anuncio) {
                    $anuncio['foto_id'] = $fotos[(int) $anuncio['id']] ?? null;
                    $anuncio['distancia_km'] = $distancias[(int) $anuncio['cidade_id']] ?? null;
                }
                unset($anuncio);
            }
        }

        return view('Home', [
            'cidade' => $cidade,
            'erro' => $erro,
            'anuncios' => $anuncios,
            'raioKm' => self::RAIO_KM,
        ]);
    }
}
