<?php

namespace App\Controllers;

use App\Libraries\DistanciaService;
use App\Models\CidadeModel;
use App\Models\UsuarioModel;
use App\Models\AnuncioModel;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\MidiaModel;
use App\Models\LojaModel;

class BuscaController extends BaseController
{
    private const DISTANCIAS_PERMITIDAS = [10, 25, 50, 75, 100];

    public function index(): string|RedirectResponse
    {
        // 1. Recebe os filtros da navbar.
        $termo = trim(
            (string) $this->request->getGet('termo')
        );

        $cidadeId = trim(
            (string) $this->request->getGet('cidade_id')
        );

        // Aceita as opções prontas e distâncias personalizadas em km inteiros.
        $raioKm = DistanciaService::normalizarRaio(
            $this->request->getGet('distancia')
        );


        // Evita pesquisas excessivamente longas.
        if (mb_strlen($termo) > 100) {
            return redirect()->to('/')
                ->with(
                    'erro',
                    'A pesquisa deve ter no máximo 100 caracteres.'
                );
        }

        $cidadeModel = new CidadeModel();
        $cidadeReferencia = null;
        $erro = null;

        // 2. Identifica a cidade de referência.
        if ($cidadeId !== '') {

            if (! ctype_digit($cidadeId)) {
                $erro = 'Selecione uma cidade válida.';
            } else {
                $cidadeReferencia = $cidadeModel->find($cidadeId);

                if (! $cidadeReferencia) {
                    $erro = 'Cidade não encontrada.';
                }
            }
        } else {

            // Utiliza a cidade cadastrada do usuário.
            $usuarioId = session()->get('usuario_id');

            if ($usuarioId) {
                $usuario = (new UsuarioModel())->find($usuarioId);

                if ($usuario && ! empty($usuario['cidade_id'])) {
                    $cidadeReferencia = $cidadeModel->find(
                        $usuario['cidade_id']
                    );
                }
            }

            if (! $cidadeReferencia) {
                $erro = 'Selecione uma cidade para pesquisar.';
            }
        }

        $anuncios = [];
        $lojas = [];
        $distancias = [];

        // 3. Executa a busca somente com cidade válida.
        if ($cidadeReferencia && ! $erro) {

            $db = db_connect();

            $distanciaService = new DistanciaService();

            $latitude = (float) $cidadeReferencia['latitude'];
            $longitude = (float) $cidadeReferencia['longitude'];

            // Obtém a fórmula SQL do serviço.
            $expressao = $distanciaService->expressaoSql('c');

            // Calcula a distância até cada município.
            $sql = "
                SELECT
                    c.codigo_ibge,
                    {$expressao} AS distancia_km
                FROM cidades c
            ";

            $resultadoCidades = $db->query(
                $sql,
                [
                    $latitude,
                    $longitude,
                    $latitude
                ]
            )->getResultArray();

            $codigosCidades = [];

            // 4. Mantém apenas cidades dentro do raio escolhido.
            foreach ($resultadoCidades as $cidade) {

                $distancia = (float) $cidade['distancia_km'];

                if ($distanciaService->dentroDoRaio(
                    $distancia,
                    $raioKm
                )) {

                    $codigo = (int) $cidade['codigo_ibge'];

                    $codigosCidades[] = $codigo;
                    $distancias[$codigo] = $distancia;
                }
            }

            // 5. Busca anúncios dessas cidades.
            if (! empty($codigosCidades)) {

                // Lojas próximas: mesmo raio e cidade dos anúncios,
                // independentemente do título pesquisado.
                $lojas = (new LojaModel())
                    ->select('lojas.id, lojas.nome_empresa, lojas.foto_perfil,
                        lojas.cidade_id, cidades.nome AS cidade_nome,
                        cidades.uf AS cidade_uf')
                    ->join('cidades', 'cidades.codigo_ibge = lojas.cidade_id')
                    ->whereIn('lojas.cidade_id', $codigosCidades)
                    ->findAll();

                foreach ($lojas as &$loja) {
                    $loja['distancia_km'] = $distancias[(int) $loja['cidade_id']];
                }
                unset($loja);

                usort($lojas, static function ($a, $b) {
                    return ($a['distancia_km'] <=> $b['distancia_km'])
                        ?: ($a['id'] <=> $b['id']);
                });
                $lojas = array_slice($lojas, 0, 3);

                $anuncioModel = new AnuncioModel();

                $consulta = $anuncioModel
                    ->select('
                        anuncios.id,
                        anuncios.titulo,
                        anuncios.ano,
                        anuncios.preco,
                        anuncios.cor,
                        anuncios.status,
                        anuncios.cidade_id,
                        anuncios.tipo_anunciante,
                        cidades.nome AS cidade_nome,
                        cidades.uf AS cidade_uf
                    ')
                    ->join(
                        'cidades',
                        'cidades.codigo_ibge = anuncios.cidade_id'
                    )
                    ->join(
                        'lojas',
                        'lojas.id = anuncios.loja_id',
                        'left'
                    )
                    ->whereIn(
                        'anuncios.cidade_id',
                        $codigosCidades
                    )
                    ->whereIn(
                        'anuncios.status',
                        ['ativo', 'pendente']
                    )
                    ->where(
                        'anuncios.data_expiracao >',
                        date('Y-m-d H:i:s')
                    )
                    ->groupStart()
                    ->where(
                        'anuncios.tipo_anunciante',
                        'pessoa'
                    )
                    ->orGroupStart()
                    ->where(
                        'anuncios.tipo_anunciante',
                        'loja'
                    )
                    ->where(
                        'lojas.deleted_at',
                        null
                    )
                    ->where(
                        'lojas.id IS NOT NULL',
                        null,
                        false
                    )
                    ->groupEnd()
                    ->groupEnd();

                // Aplica o nome do veículo, se informado.
                if ($termo !== '') {
                    $consulta->like(
                        'anuncios.titulo',
                        $termo
                    );
                }

                // Consulta os anúncios não excluídos.
                $anuncios = $consulta
                    ->orderBy('anuncios.id', 'DESC')
                    ->findAll();

                // 6. Acrescenta a distância aos anúncios.
                foreach ($anuncios as &$anuncio) {
                    $codigo = (int) $anuncio['cidade_id'];

                    $anuncio['distancia_km'] =
                        $distancias[$codigo] ?? null;
                }

                unset($anuncio);

                // Buscar a primeira foto de cada anúncio encontrado.
                $midiaModel = new MidiaModel();

                foreach ($anuncios as &$anuncio) {

                    $primeiraFoto = $midiaModel
                        ->where('anuncio_id', $anuncio['id'])
                        ->where('tipo', 'foto')
                        ->orderBy('ordem', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->first();

                    $anuncio['foto_id'] = $primeiraFoto
                        ? (int) $primeiraFoto['id']
                        : null;
                }

                unset($anuncio);

                // Ordena do anúncio mais próximo ao distante.
                usort($anuncios, static function ($a, $b) {
                    return ($a['distancia_km'] ?? INF)
                        <=>
                        ($b['distancia_km'] ?? INF);
                });
            }
        }

        // 7. Envia os resultados para a View.
        return view('anuncios/BuscarAnuncios', [
            'termo' => $termo,
            'cidade' => $cidadeReferencia,
            'erro' => $erro,
            'anuncios' => $anuncios,
            'lojas' => $lojas,
            'raioKm' => $raioKm,
            'cidadeIdFiltro' => $cidadeId,
            'proximaDistancia' => $this->proximaDistancia($raioKm),
        ]);
    }

    private function proximaDistancia(int $raioAtual): ?int
    {
        foreach (self::DISTANCIAS_PERMITIDAS as $distancia) {
            if ($distancia > $raioAtual) {
                return $distancia;
            }
        }

        return null;
    }

}
