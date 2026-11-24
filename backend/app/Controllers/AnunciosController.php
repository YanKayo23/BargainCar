<?php

namespace App\Controllers;

use App\Models\AnuncioModel;
use App\Models\CidadeModel;
use App\Models\LojaModel;
use App\Models\MidiaModel;
use App\Models\UsuarioModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\AnuncioCicloVidaService as Ciclo;
use RuntimeException;
use Throwable;

class AnunciosController extends BaseController
{
    private const MIN_FOTOS = 3;
    private const MAX_FOTOS = 5;
    private const MAX_FOTO_BYTES = 5 * 1024 * 1024;
    private const MAX_VIDEO_BYTES = 50 * 1024 * 1024;

    public function criar(): string|RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');
        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $usuario = (new UsuarioModel())->find($usuarioId);
        if (! $usuario) {
            return redirect()->to('/login');
        }

        $loja = (new LojaModel())
            ->where('usuario_id', $usuarioId)
            ->first();

        // A view pode usar estes dados para preencher os campos do anunciante.
        return view('anuncios/CriarAnuncio', [
            'usuario' => $usuario,
            'loja' => $loja,
            'tipoInicial' => ($loja && session()->get('modo_perfil') === 'loja') ? 'loja' : 'pessoa',
        ]);
    }

    public function salvar(): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');
        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $usuario = (new UsuarioModel())->find($usuarioId);
        if (! $usuario) {
            return redirect()->to('/login');
        }

        $dados = $this->request->getPost();
        // O anunciante deve corresponder ao perfil ativo na sessão.
        // Nunca confiar somente no campo enviado pelo navegador.
        $lojaAtiva = (new LojaModel())->where('usuario_id', $usuarioId)->first();
        $dados['tipo_anunciante'] = ($lojaAtiva && session()->get('modo_perfil') === 'loja')
            ? 'loja' : 'pessoa';
        $regras = [
            'tipo_anunciante' => 'required|in_list[pessoa,loja]',
            'titulo' => 'required|max_length[150]',
            'ano' => 'required|integer|greater_than_equal_to[1900]|less_than_equal_to[' . (date('Y') + 1) . ']',
            'preco' => 'required|numeric|greater_than[0]|less_than_equal_to[9999999999.99]',
            'cor' => 'required|max_length[50]',
            'descricao' => 'required',
            'defeitos' => 'permit_empty',
            'debitos_pendentes' => 'permit_empty',
            'situacao_documentacao' => 'required|max_length[100]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato' => 'required|max_length[30]',
        ];

        if (! $this->validateData($dados, $regras)) {
            return redirect()->back()->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $validado = $this->validator->getValidated();
        $lojaId = null;

        if ($validado['tipo_anunciante'] === 'loja') {
            $loja = (new LojaModel())
                ->where('usuario_id', $usuarioId)
                ->first();

            if (! $loja) {
                return $this->erro('loja', 'Você precisa possuir uma loja ativa para anunciar como empresa.');
            }
            $lojaId = $loja['id'];
        }

        $fotos = $this->request->getFileMultiple('fotos') ?? [];
        if (count($fotos) < self::MIN_FOTOS || count($fotos) > self::MAX_FOTOS) {
            return $this->erro('fotos', 'Envie no mínimo 3 e no máximo 5 fotos.');
        }

        $mimesFotos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        foreach ($fotos as $indice => $foto) {
            if (! $foto instanceof UploadedFile || ! $foto->isValid()) {
                return $this->erro('fotos', 'Não foi possível receber a foto ' . ($indice + 1) . '.');
            }
            if ($foto->getSize() > self::MAX_FOTO_BYTES) {
                return $this->erro('fotos', 'Cada foto deve ter no máximo 5 MB.');
            }
            if (! isset($mimesFotos[$foto->getMimeType()])) {
                return $this->erro('fotos', 'As fotos devem ser JPG, PNG ou WebP.');
            }
            if (@getimagesize($foto->getTempName()) === false) {
                return $this->erro('fotos', 'Uma das fotos enviadas não é uma imagem válida.');
            }
        }

        $video = $this->request->getFile('video');
        $temVideo = $video !== null && $video->getError() !== UPLOAD_ERR_NO_FILE;
        if ($temVideo) {
            if (! $video->isValid()) {
                return $this->erro('video', 'Não foi possível receber o vídeo.');
            }
            if ($video->getSize() > self::MAX_VIDEO_BYTES) {
                return $this->erro('video', 'O vídeo deve ter no máximo 50 MB.');
            }
            if ($video->getMimeType() !== 'video/mp4') {
                return $this->erro('video', 'O vídeo deve estar no formato MP4.');
            }
        }

        $pastaBase = WRITEPATH . 'uploads/anuncios/';
        if (! is_dir($pastaBase) && ! mkdir($pastaBase, 0750, true) && ! is_dir($pastaBase)) {
            return $this->erro('arquivos', 'Não foi possível preparar o armazenamento.');
        }

        $pastaAnuncio = null;
        $db = db_connect();
        $transacaoIniciada = false;

        try {
            $db->transBegin();
            $transacaoIniciada = true;

            $anuncioModel = new AnuncioModel();
            $midiaModel = new MidiaModel();

            $anuncioId = $anuncioModel->insert([
                'tipo_anunciante' => $validado['tipo_anunciante'],
                'usuario_id' => $usuarioId,
                'loja_id' => $lojaId,
                'cidade_id' => $validado['cidade_id'],
                'contato' => $validado['contato'],
                'data_expiracao' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'titulo' => $validado['titulo'],
                'ano' => $validado['ano'],
                'preco' => $validado['preco'],
                'cor' => $validado['cor'],
                'descricao' => $validado['descricao'],
                'defeitos' => $validado['defeitos'] ?? null,
                'debitos_pendentes' => $validado['debitos_pendentes'] ?? null,
                'situacao_documentacao' => $validado['situacao_documentacao'],
                'status' => 'ativo',
            ]);

            if (! $anuncioId) {
                throw new RuntimeException('Falha ao registrar o anúncio.');
            }

            $pastaAnuncio = $pastaBase . $anuncioId . '/';
            if (! is_dir($pastaAnuncio) && ! mkdir($pastaAnuncio, 0750, true)) {
                throw new RuntimeException('Falha ao criar a pasta do anúncio.');
            }

            foreach ($fotos as $indice => $foto) {
                $extensao = $mimesFotos[$foto->getMimeType()];
                $nome = bin2hex(random_bytes(16)) . '.' . $extensao;
                $foto->move($pastaAnuncio, $nome);
                if (! is_file($pastaAnuncio . $nome)) {
                    throw new RuntimeException('Falha ao armazenar uma foto.');
                }
                if (! $midiaModel->insert([
                    'anuncio_id' => $anuncioId,
                    'tipo' => 'foto',
                    'caminho_arquivo' => 'anuncios/' . $anuncioId . '/' . $nome,
                    'ordem' => $indice + 1,
                ])) {
                    throw new RuntimeException('Falha ao registrar uma foto.');
                }
            }

            if ($temVideo) {
                $nome = bin2hex(random_bytes(16)) . '.mp4';
                $video->move($pastaAnuncio, $nome);
                if (! is_file($pastaAnuncio . $nome)) {
                    throw new RuntimeException('Falha ao armazenar o vídeo.');
                }
                if (! $midiaModel->insert([
                    'anuncio_id' => $anuncioId,
                    'tipo' => 'video',
                    'caminho_arquivo' => 'anuncios/' . $anuncioId . '/' . $nome,
                    'ordem' => 1,
                ])) {
                    throw new RuntimeException('Falha ao registrar o vídeo.');
                }
            }

            if ($db->transStatus() === false || $db->transCommit() === false) {
                throw new RuntimeException('Falha ao confirmar o cadastro.');
            }
            $transacaoIniciada = false;

            return redirect()->to('/')
                ->with('sucesso', 'Anúncio criado com sucesso!');
        } catch (Throwable $e) {
            if ($transacaoIniciada) {
                $db->transRollback();
            }
            if ($pastaAnuncio !== null && is_dir($pastaAnuncio)) {
                foreach (glob($pastaAnuncio . '*') ?: [] as $arquivo) {
                    if (is_file($arquivo)) {
                        @unlink($arquivo);
                    }
                }
                @rmdir($pastaAnuncio);
            }
            log_message('error', 'Erro ao criar anúncio: ' . $e->getMessage());
            return $this->erro('cadastro', 'Não foi possível criar o anúncio. Tente novamente.');
        }
    }

    public function ver(int $id): string
    {
        $anuncio = (new AnuncioModel())->find($id);
        if (
            ! $anuncio || $anuncio['status'] !== 'ativo'
            || empty($anuncio['data_expiracao'])
            || strtotime($anuncio['data_expiracao']) <= time()
        ) {
            throw PageNotFoundException::forPageNotFound('Anúncio não encontrado ou indisponível.');
        }

        $nomeAnunciante = 'Anunciante';
        $perfilId = null;

        if ($anuncio['tipo_anunciante'] === 'loja') {
            $loja = ! empty($anuncio['loja_id'])
                ? (new LojaModel())->where('id', $anuncio['loja_id'])
                ->where('usuario_id', $anuncio['usuario_id'])->first()
                : null;
            if (! $loja) {
                throw PageNotFoundException::forPageNotFound('Este anúncio não está disponível.');
            }
            $nomeAnunciante = $loja['nome_empresa'];
            $perfilId = (int) $loja['id'];
        } else {
            $usuario = (new UsuarioModel())->find($anuncio['usuario_id']);
            if ($usuario) {
                $nomeAnunciante = $usuario['nome'];
                $perfilId = (int) $usuario['id'];
            }
        }

        $midias = (new MidiaModel())
            ->where('anuncio_id', $id)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $fotos = [];
        $video = null;
        foreach ($midias as $midia) {
            if ($midia['tipo'] === 'foto') {
                $fotos[] = $midia;
            } elseif ($midia['tipo'] === 'video' && $video === null) {
                $video = $midia;
            }
        }

        return view('anuncios/VerAnuncio', [
            'anuncio' => $anuncio,
            'cidade' => (new CidadeModel())->find($anuncio['cidade_id']),
            'fotos' => $fotos,
            'video' => $video,
            'nomeAnunciante' => $nomeAnunciante,
            'perfilId' => $perfilId,
        ]);
    }

    public function exibirMidia(int $anuncioId, int $midiaId): ResponseInterface
    {
        $anuncio = (new AnuncioModel())->find($anuncioId);
        if (
            ! $anuncio || $anuncio['status'] !== 'ativo'
            || empty($anuncio['data_expiracao'])
            || strtotime($anuncio['data_expiracao']) <= time()
        ) {
            return $this->response->setStatusCode(404);
        }

        if ($anuncio['tipo_anunciante'] === 'loja') {
            $loja = ! empty($anuncio['loja_id'])
                ? (new LojaModel())->where('id', $anuncio['loja_id'])
                ->where('usuario_id', $anuncio['usuario_id'])->first()
                : null;
            if (! $loja) {
                return $this->response->setStatusCode(404);
            }
        }

        return $this->servirMidiaArmazenada($anuncioId, $midiaId);
    }


    // Autoriza anúncios pessoais e anúncios da loja ativa do proprietário.
    private function anuncioGerenciavel(int $id, $usuarioId): ?array
    {
        if (! $usuarioId) return null;
        $anuncio = (new AnuncioModel())->where('id', $id)
            ->where('usuario_id', $usuarioId)->first();
        if (! $anuncio || Ciclo::situacao($anuncio) !== null) return null;
        if ($anuncio['tipo_anunciante'] === 'pessoa') return $anuncio;
        if ($anuncio['tipo_anunciante'] !== 'loja' || empty($anuncio['loja_id'])) return null;
        $loja = (new LojaModel())->where('id', $anuncio['loja_id'])
            ->where('usuario_id', $usuarioId)->where('deleted_at', null)->first();
        return $loja ? $anuncio : null;
    }

    private function perfilDoAnuncio(array $anuncio): string
    {
        $loja = $anuncio['tipo_anunciante'] === 'loja';
        session()->set('modo_perfil', $loja ? 'loja' : 'pessoa');
        return site_url($loja ? 'perfil-loja' : 'meu-perfil');
    }

    // Aceita apenas destinos internos conhecidos e compatíveis com o anúncio.
    private function origemEdicao(array $anuncio, mixed $origem): string
    {
        if ($origem === 'anuncio' && $anuncio['status'] === 'ativo'
            && ! empty($anuncio['data_expiracao'])
            && strtotime($anuncio['data_expiracao']) > time()) {
            return 'anuncio';
        }
        return $anuncio['tipo_anunciante'] === 'loja' ? 'loja' : 'perfil';
    }

    private function destinoEdicao(array $anuncio, string $origem): string
    {
        return site_url(match ($origem) {
            'anuncio' => 'anuncios/' . $anuncio['id'],
            'loja' => 'perfil-loja',
            default => 'meu-perfil',
        });
    }

    // Edição de anúncios (não altera status ou expiração).
    public function editar(int $id): string|RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');
        if (! $usuarioId) {
            return redirect()->to('/login');
        }
        $anuncio = $this->anuncioGerenciavel($id, $usuarioId);
        if (! $anuncio) {
            return redirect()->to('/meu-perfil')->with('erro', 'Anúncio não encontrado ou acesso não permitido.');
        }
        $midias = (new MidiaModel())->where('anuncio_id', $id)
            ->orderBy('ordem', 'ASC')->orderBy('id', 'ASC')->findAll();
        $fotos = [];
        $video = null;
        foreach ($midias as $midia) {
            if ($midia['tipo'] === 'foto') {
                $fotos[] = $midia;
            } elseif ($midia['tipo'] === 'video' && $video === null) {
                $video = $midia;
            }
        }
        $this->perfilDoAnuncio($anuncio);
        $origem = $this->origemEdicao($anuncio, $this->request->getGet('origem'));
        return view('anuncios/EditarAnuncio', [
            'origem' => $origem,
            'urlRetorno' => $this->destinoEdicao($anuncio, $origem),
            'anuncio' => $anuncio,
            'cidade' => (new CidadeModel())->find($anuncio['cidade_id']),
            'fotos' => $fotos,
            'video' => $video,
        ]);
    }

    // Acesso às mídias na edição, inclusive de anúncios expirados.
    public function exibirMidiaEdicao(int $anuncioId, int $midiaId): ResponseInterface
    {
        $usuarioId = session()->get('usuario_id');
        $anuncio = $this->anuncioGerenciavel($anuncioId, $usuarioId);
        if (! $anuncio) {
            return $this->response->setStatusCode(404);
        }
        return $this->servirMidiaArmazenada($anuncioId, $midiaId);
    }

    public function exibirMidiaHistorico(int $anuncioId, int $midiaId): ResponseInterface
    {
        $anuncio = $this->anuncioHistorico($anuncioId, false);
        if (!$anuncio) return $this->response->setStatusCode(404);
        return $this->servirMidiaArmazenada($anuncioId, $midiaId);
    }

    private function servirMidiaArmazenada(int $anuncioId, int $midiaId): ResponseInterface
    {
        $midia = (new MidiaModel())->where('id', $midiaId)
            ->where('anuncio_id', $anuncioId)->first();
        if (! $midia) {
            return $this->response->setStatusCode(404);
        }
        $tipos = [
            'foto' => ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'],
            'video' => ['mp4' => 'video/mp4'],
        ];
        $tipo = $midia['tipo'];
        $relativo = str_replace('\\', '/', (string) $midia['caminho_arquivo']);
        $extensao = strtolower(pathinfo($relativo, PATHINFO_EXTENSION));
        if (! isset($tipos[$tipo][$extensao])) {
            return $this->response->setStatusCode(404);
        }
        $nome = basename($relativo);
        if ($relativo !== "anuncios/{$anuncioId}/{$nome}") {
            return $this->response->setStatusCode(404);
        }
        $diretorio = realpath(WRITEPATH . 'uploads/anuncios/' . $anuncioId);
        if ($diretorio === false) {
            return $this->response->setStatusCode(404);
        }
        $arquivo = realpath($diretorio . DIRECTORY_SEPARATOR . $nome);
        if ($arquivo === false || dirname($arquivo) !== $diretorio || ! is_file($arquivo) || ! is_readable($arquivo)) {
            return $this->response->setStatusCode(404);
        }
        $mimeEsperado = $tipos[$tipo][$extensao];
        if (mime_content_type($arquivo) !== $mimeEsperado) {
            return $this->response->setStatusCode(404);
        }
        return $this->response->setHeader('Content-Type', $mimeEsperado)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(file_get_contents($arquivo));
    }

    public function atualizar(int $id): RedirectResponse
    {
        return $this->salvarRevisao($id, false);
    }

    public function republicar(int $id): RedirectResponse
    {
        return $this->salvarRevisao($id, true);
    }

    private function salvarRevisao(int $id, bool $republicar): RedirectResponse
    {
        $usuarioId = session()->get('usuario_id');
        if (! $usuarioId) {
            return redirect()->to('/login');
        }
        $anuncioModel = new AnuncioModel();
        $anuncio = ($republicar ? $this->anuncioHistorico($id) : $this->anuncioGerenciavel($id, $usuarioId));
        if (! $anuncio || ($republicar && Ciclo::acao($anuncio) !== 'republicar')) {
            return redirect()->to('/meu-perfil')->with('erro', 'Anúncio não encontrado ou acesso não permitido.');
        }
        $origem = $this->origemEdicao($anuncio, $this->request->getPost('origem'));
        $urlEdicao = site_url('anuncios/' . $id . ($republicar ? '/republicar' : '/editar')) . '?origem=' . $origem;
        $erroEdicao = static function (string $campo, string $mensagem) use ($urlEdicao): RedirectResponse {
            return redirect()->to($urlEdicao)->withInput()->with('erros', [$campo => $mensagem]);
        };
        $dados = $this->request->getPost();
        if ($republicar && $this->request->getPost('confirmar') !== '1') return $erroEdicao('confirmar', 'Confirme a republicação.');
        $regras = [
            'titulo' => 'required|max_length[150]',
            'ano' => 'required|integer|greater_than_equal_to[1900]|less_than_equal_to[' . (date('Y') + 1) . ']',
            'preco' => 'required|numeric|greater_than[0]|less_than_equal_to[9999999999.99]',
            'cor' => 'required|max_length[50]',
            'descricao' => 'required',
            'defeitos' => 'permit_empty',
            'debitos_pendentes' => 'permit_empty',
            'situacao_documentacao' => 'required|in_list[regular,pendente]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato' => 'required|max_length[30]',
        ];
        if (! $this->validateData($dados, $regras)) {
            return redirect()->to($urlEdicao)->withInput()->with('erros', $this->validator->getErrors());
        }
        $validado = $this->validator->getValidated();
        $midiaModel = new MidiaModel();
        $midias = $midiaModel->where('anuncio_id', $id)->orderBy('ordem','ASC')->orderBy('id','ASC')->findAll();
        $fotosAtuais = [];
        $videoAtual = null;
        foreach ($midias as $midia) {
            if ($midia['tipo'] === 'foto') {
                $fotosAtuais[(int) $midia['id']] = $midia;
            } elseif ($midia['tipo'] === 'video' && $videoAtual === null) {
                $videoAtual = $midia;
            }
        }
        $idsRemover = $this->request->getPost('remover_fotos') ?? [];
        if (! is_array($idsRemover)) {
            return $erroEdicao('fotos', 'Seleção de fotos inválida.');
        }
        $idsRemover = array_values(array_unique(array_map(static fn($v) => (string)(int)$v, $idsRemover)));
        foreach ($idsRemover as $midiaId) {
            if (! ctype_digit($midiaId) || ! isset($fotosAtuais[(int) $midiaId])) {
                return $erroEdicao('fotos', 'Uma das fotos selecionadas não pertence ao anúncio.');
            }
        }
        $fotosNovas = array_values(array_filter(
            $this->request->getFileMultiple('fotos') ?? [],
            static fn($foto) => $foto instanceof UploadedFile && $foto->getError() !== UPLOAD_ERR_NO_FILE
        ));
        $quantidadeFinal = count($fotosAtuais) - count($idsRemover) + count($fotosNovas);
        if ($quantidadeFinal < self::MIN_FOTOS || $quantidadeFinal > self::MAX_FOTOS) {
            return $erroEdicao('fotos', 'O anúncio deve permanecer com 3 a 5 fotos.');
        }
        $mimesFotos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        foreach ($fotosNovas as $foto) {
            if (
                ! $foto->isValid() || $foto->getSize() === 0 || $foto->getSize() > self::MAX_FOTO_BYTES
                || ! isset($mimesFotos[$foto->getMimeType()])
                || @getimagesize($foto->getTempName()) === false
            ) {
                return $erroEdicao('fotos', 'Foto inválida. Use JPG, PNG ou WebP, com até 5 MB por foto.');
            }
        }
        $videoNovo = $this->request->getFile('video');
        $temVideoNovo = $videoNovo !== null && $videoNovo->getError() !== UPLOAD_ERR_NO_FILE;
        if ($temVideoNovo && (! $videoNovo->isValid() || $videoNovo->getSize() === 0
            || $videoNovo->getSize() > self::MAX_VIDEO_BYTES || $videoNovo->getMimeType() !== 'video/mp4'
            || ! preg_match('/\\.mp4$/i', $videoNovo->getClientName()))) {
            return $erroEdicao('video', 'Vídeo inválido. Use MP4 com até 50 MB.');
        }
        $removerVideo = $this->request->getPost('remover_video') === '1';
        $pastaAnuncio = WRITEPATH . 'uploads/anuncios/' . $id . '/';
        if (! is_dir($pastaAnuncio) && ! mkdir($pastaAnuncio, 0750, true) && ! is_dir($pastaAnuncio)) {
            return $erroEdicao('arquivos', 'Não foi possível preparar o armazenamento.');
        }
        $novosArquivos = [];
        $arquivosAntigos = [];
        $db = db_connect();
        $transacaoIniciada = false;
        try {
            $db->transBegin();
            $transacaoIniciada = true;
            if ($republicar) {
                $this->bloquearHistorico($db, $anuncio, 'republicar');
            }
            $campos = [
                'titulo' => $validado['titulo'],
                'ano' => $validado['ano'],
                'preco' => $validado['preco'],
                'cor' => $validado['cor'],
                'descricao' => $validado['descricao'],
                'defeitos' => $validado['defeitos'] ?? null,
                'debitos_pendentes' => $validado['debitos_pendentes'] ?? null,
                'situacao_documentacao' => $validado['situacao_documentacao'],
                'cidade_id' => $validado['cidade_id'],
                'contato' => $validado['contato'],
            ];
            if ($republicar) {
                $novoId = $anuncioModel->insert($campos + [
                    'usuario_id' => $anuncio['usuario_id'], 'loja_id' => $anuncio['loja_id'],
                    'tipo_anunciante' => $anuncio['tipo_anunciante'], 'anuncio_origem_id' => $id,
                    'status' => 'ativo', 'data_expiracao' => date('Y-m-d H:i:s', strtotime('+30 days')),
                ]);
                if (!$novoId) throw new RuntimeException('Falha ao criar publicação.');
                $pastaAnuncio = WRITEPATH . 'uploads/anuncios/' . $novoId . '/';
                if (!mkdir($pastaAnuncio, 0750, true)) throw new RuntimeException('Falha ao criar pasta.');
                foreach ($fotosAtuais as $fotoId => $fotoAtual) {
                    if (!in_array((string)$fotoId, $idsRemover, true)) {
                        $this->copiarMidia($fotoAtual, $id, $novoId, $pastaAnuncio, $novosArquivos, $midiaModel);
                    }
                }
                if ($videoAtual && !$removerVideo && !$temVideoNovo) {
                    $this->copiarMidia($videoAtual, $id, $novoId, $pastaAnuncio, $novosArquivos, $midiaModel);
                }
                $id = (int)$novoId;
                $ordem = count($fotosAtuais) - count($idsRemover) + 1;
            } elseif (!$anuncioModel->update($id, $campos)) {
                throw new RuntimeException('Falha ao atualizar os dados.');
            }
            if (!$republicar) {
            foreach ($idsRemover as $midiaId) {
                $midia = $fotosAtuais[(int) $midiaId];
                if (! $midiaModel->delete((int) $midiaId)) {
                    throw new RuntimeException('Falha ao remover registro de foto.');
                }
                $arquivosAntigos[] = $pastaAnuncio . basename($midia['caminho_arquivo']);
            }
            $ordem = 1;
            foreach ($fotosAtuais as $fotoId => $fotoAtual) {
                if (in_array((string) $fotoId, $idsRemover, true)) {
                    continue;
                }
                if (! $midiaModel->update($fotoId, ['ordem' => $ordem++])) {
                    throw new RuntimeException('Falha ao ordenar as fotos.');
                }
            }
            }
            foreach ($fotosNovas as $foto) {
                $nome = bin2hex(random_bytes(16)) . '.' . $mimesFotos[$foto->getMimeType()];
                $foto->move($pastaAnuncio, $nome);
                $caminho = $pastaAnuncio . $nome;
                $novosArquivos[] = $caminho;
                if (! is_file($caminho) || ! $midiaModel->insert([
                    'anuncio_id' => $id,
                    'tipo' => 'foto',
                    'caminho_arquivo' => 'anuncios/' . $id . '/' . $nome,
                    'ordem' => $ordem++,
                ])) {
                    throw new RuntimeException('Falha ao salvar nova foto.');
                }
            }
            if (!$republicar && $videoAtual && ($removerVideo || $temVideoNovo)) {
                if (! $midiaModel->delete((int) $videoAtual['id'])) {
                    throw new RuntimeException('Falha ao remover vídeo antigo.');
                }
                $arquivosAntigos[] = $pastaAnuncio . basename($videoAtual['caminho_arquivo']);
            }
            if ($temVideoNovo) {
                $nome = bin2hex(random_bytes(16)) . '.mp4';
                $videoNovo->move($pastaAnuncio, $nome);
                $caminho = $pastaAnuncio . $nome;
                $novosArquivos[] = $caminho;
                if (! is_file($caminho) || ! $midiaModel->insert([
                    'anuncio_id' => $id,
                    'tipo' => 'video',
                    'caminho_arquivo' => 'anuncios/' . $id . '/' . $nome,
                    'ordem' => 1,
                ])) {
                    throw new RuntimeException('Falha ao salvar novo vídeo.');
                }
            }
            if ($db->transStatus() === false || $db->transCommit() === false) {
                throw new RuntimeException('Falha ao confirmar a edição.');
            }
            $transacaoIniciada = false;
            foreach ($arquivosAntigos as $arquivo) {
                if (is_file($arquivo)) {
                    @unlink($arquivo);
                }
            }
            $this->perfilDoAnuncio($anuncio);
            $origem = $this->origemEdicao($anuncio, $origem);
            return redirect()->to($this->destinoEdicao($anuncio, $origem))
                ->with('sucesso', $republicar ? 'Anúncio republicado com sucesso!' : 'Anúncio atualizado com sucesso!');
        } catch (Throwable $e) {
            if ($transacaoIniciada) {
                $db->transRollback();
            }
            foreach ($novosArquivos as $arquivo) {
                if (is_file($arquivo)) {
                    @unlink($arquivo);
                }
            }
            log_message('error', 'Erro ao editar anúncio ' . $id . ': ' . $e->getMessage());
            return $erroEdicao('edicao', 'Não foi possível salvar as alterações. Tente novamente.');
        }
    }

    public function excluir(int $id): RedirectResponse
    {
        // Verifica se o usuário está autenticado.
        $usuarioId = session()->get('usuario_id');

        if (! $usuarioId) {
            return redirect()->to('/login');
        }

        $anuncioModel = new AnuncioModel();

        $anuncio = $this->anuncioGerenciavel($id, $usuarioId);

        if (! $anuncio) {
            return redirect()->to('/meu-perfil')
                ->with('erro', 'Anúncio não encontrado ou acesso não permitido.');
        }

        // Exclusão lógica: preenche deleted_at.
        if (! $anuncioModel->where('deleted_at', null)->update($id, ['deleted_at' => date('Y-m-d H:i:s'), 'motivo_indisponibilidade' => 'exclusao_vendedor'])) {
            return redirect()->to($this->perfilDoAnuncio($anuncio))
                ->with('erro', 'Não foi possível excluir o anúncio.');
        }

        return redirect()->to($this->perfilDoAnuncio($anuncio))
            ->with('sucesso', 'Anúncio excluído com sucesso!');
    }


    private function anuncioHistorico(int $id, bool $lojaAtiva = true): ?array
    {
        $uid = (int)session()->get('usuario_id');
        if (!$uid) return null;
        $a = (new AnuncioModel())->withDeleted()->where('usuario_id', $uid)->find($id);
        if (!$a || Ciclo::situacao($a) === null) return null;
        if ($a['tipo_anunciante'] === 'loja') {
            $model = new LojaModel();
            if (!$lojaAtiva) $model->withDeleted();
            if (!$model->where('usuario_id', $uid)->find($a['loja_id'])) return null;
        }
        return $a;
    }

    private function bloquearHistorico($db, array $a, string $acao): array
    {
        if ($a['tipo_anunciante'] === 'loja') {
            $loja = $db->query('SELECT id FROM lojas WHERE id = ? AND usuario_id = ? AND deleted_at IS NULL FOR UPDATE', [$a['loja_id'], $a['usuario_id']])->getRowArray();
            if (!$loja) throw new RuntimeException('Loja indisponível.');
        }
        $atual = $db->query('SELECT * FROM anuncios WHERE id = ? AND usuario_id = ? FOR UPDATE', [$a['id'], $a['usuario_id']])->getRowArray();
        if (!$atual || Ciclo::acao($atual) !== $acao) throw new RuntimeException('Anúncio indisponível.');
        return $atual;
    }

    public function restaurar(int $id): RedirectResponse
    {
        $a = $this->anuncioHistorico($id);
        if (!$a || Ciclo::acao($a) !== 'restaurar' || $this->request->getPost('confirmar') !== '1') return redirect()->to(site_url('meu-perfil'))->with('erro','Restauração não permitida.');
        $destino = $this->perfilDoAnuncio($a);
        $db = db_connect();
        $db->transBegin();
        try {
            $a = $this->bloquearHistorico($db, $a, 'restaurar');
            $fotos = (new MidiaModel())->where('anuncio_id',$id)->where('tipo','foto')->findAll();
            if (count($fotos) < 3 || count($fotos) > 5) throw new RuntimeException('Quantidade de fotos inválida.');
            foreach ($fotos as $foto) $this->arquivoHistorico($foto, $id);
            $agora = time();
            if (strtotime($a['data_expiracao']) <= $agora) throw new RuntimeException('Prazo encerrado.');
            $db->table('anuncios')->where('id',$id)->where('data_expiracao >',date('Y-m-d H:i:s',$agora))->update([
                'deleted_at'=>null,'motivo_indisponibilidade'=>null,
                'status'=>strtotime($a['data_expiracao']) <= $agora + 259200 ? 'pendente' : 'ativo',
            ]);
            if ($db->affectedRows() !== 1 || !$db->transStatus() || !$db->transCommit()) throw new RuntimeException('Falha na restauração.');
            return redirect()->to($destino)->with('sucesso','Anúncio restaurado com sucesso!');
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', $e->getMessage());
            return redirect()->to($destino)->with('erro','Não foi possível restaurar o anúncio. Confira a validade e as mídias.');
        }
    }

    public function prepararRepublicacao(int $id): string|RedirectResponse
    {
        $a = $this->anuncioHistorico($id);
        if (!$a || Ciclo::acao($a) !== 'republicar') return redirect()->to(site_url('meu-perfil'))->with('erro','Republicação não permitida.');
        $midias = (new MidiaModel())->where('anuncio_id',$id)->orderBy('ordem','ASC')->orderBy('id','ASC')->findAll();
        $fotos = array_values(array_filter($midias, static fn($m) => $m['tipo'] === 'foto'));
        $videos = array_values(array_filter($midias, static fn($m) => $m['tipo'] === 'video'));
        return view('anuncios/EditarAnuncio', ['republicar'=>true,'anuncio'=>$a,'fotos'=>$fotos,'video'=>$videos[0] ?? null,'cidade'=>(new CidadeModel())->find($a['cidade_id']),'urlRetorno'=>$this->perfilDoAnuncio($a),'origem'=>$a['tipo_anunciante']==='loja'?'loja':'perfil']);
    }

    private function arquivoHistorico(array $m, int $id): string
    {
        $rel = str_replace('\\','/', $m['caminho_arquivo']);
        $nome = basename($rel);
        $dir = realpath(WRITEPATH . 'uploads/anuncios/' . $id);
        $arquivo = $dir ? realpath($dir . DIRECTORY_SEPARATOR . $nome) : false;
        $tipos = $m['tipo'] === 'foto' ? ['image/jpeg','image/png','image/webp'] : ['video/mp4'];
        if ($rel !== "anuncios/$id/$nome" || !$arquivo || dirname($arquivo) !== $dir || !is_file($arquivo) || !in_array(mime_content_type($arquivo),$tipos,true) || filesize($arquivo) <= 0 || filesize($arquivo) > ($m['tipo']==='foto'?self::MAX_FOTO_BYTES:self::MAX_VIDEO_BYTES)) throw new RuntimeException('Mídia original inválida.');
        return $arquivo;
    }

    private function copiarMidia(array $m, int $id, int $novoId, string $pasta, array &$arquivos, MidiaModel $model): void
    {
        $arquivo = $this->arquivoHistorico($m,$id);
        $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','video/mp4'=>'mp4'][mime_content_type($arquivo)];
        $nome = bin2hex(random_bytes(16)) . '.' . $ext;
        $arquivos[] = $pasta . $nome;
        if (!copy($arquivo,$pasta.$nome) || !$model->insert(['anuncio_id'=>$novoId,'tipo'=>$m['tipo'],'ordem'=>$m['ordem'],'caminho_arquivo'=>'anuncios/'.$novoId.'/'.$nome])) throw new RuntimeException('Falha ao copiar mídia.');
    }

    private function erro(string $campo, string $mensagem): RedirectResponse
    {
        return redirect()->back()->withInput()
            ->with('erros', [$campo => $mensagem]);
    }
}
