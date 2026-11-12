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
            'tipoInicial' => $loja ? 'loja' : 'pessoa',
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
        if (! $anuncio || $anuncio['status'] !== 'ativo'
            || empty($anuncio['data_expiracao'])
            || strtotime($anuncio['data_expiracao']) <= time()) {
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
        if (! $anuncio || $anuncio['status'] !== 'ativo'
            || empty($anuncio['data_expiracao'])
            || strtotime($anuncio['data_expiracao']) <= time()) {
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

        $midia = (new MidiaModel())->where('id', $midiaId)
            ->where('anuncio_id', $anuncioId)->first();
        if (! $midia) {
            return $this->response->setStatusCode(404);
        }

        $tipos = [
            'foto' => [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ],
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
        if ($arquivo === false || dirname($arquivo) !== $diretorio
            || ! is_file($arquivo) || ! is_readable($arquivo)) {
            return $this->response->setStatusCode(404);
        }

        $mimeEsperado = $tipos[$tipo][$extensao];
        if (mime_content_type($arquivo) !== $mimeEsperado) {
            return $this->response->setStatusCode(404);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeEsperado)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(file_get_contents($arquivo));
    }

    private function erro(string $campo, string $mensagem): RedirectResponse
    {
        return redirect()->back()->withInput()
            ->with('erros', [$campo => $mensagem]);
    }
}
