<?php

namespace App\Controllers;

use App\Models\AnuncioModel;
use App\Models\LojaModel;
use App\Models\MidiaModel;
use App\Models\UsuarioModel;
use App\Models\CidadeModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Files\UploadedFile;
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

        $usuarioModel = new UsuarioModel();
        $lojaModel = new LojaModel();
        $cidadeModel = new CidadeModel();

        $usuario = $usuarioModel->find($usuarioId);

        if (! $usuario) {
            return redirect()->to('/login');
        }

        // Busca somente a loja ativa
        $loja = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->where('deleted_at', null)
            ->first();

        // Busca a cidade cadastrada no perfil
        $cidadeUsuario = null;

        if (! empty($usuario['cidade_id'])) {
            $cidadeUsuario = $cidadeModel->find($usuario['cidade_id']);
        }

        // Busca a cidade cadastrada na loja
        $cidadeLoja = null;

        if ($loja && ! empty($loja['cidade_id'])) {
            $cidadeLoja = $cidadeModel->find($loja['cidade_id']);
        }

        return view('anuncios/CriarAnuncio', [
            'usuario' => $usuario,
            'loja' => $loja,
            'cidadeUsuario' => $cidadeUsuario,
            'cidadeLoja' => $cidadeLoja,
        ]);
    }


    public function salvar(): RedirectResponse
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

        $dados = $this->request->getPost();

        // 1. Validar informações do formulário
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
            return redirect()->back()
                ->withInput()
                ->with('erros', $this->validator->getErrors());
        }

        $validado = $this->validator->getValidated();

        // 2. Verificar se a loja pertence ao usuário e está ativa
        $lojaId = null;

        if ($validado['tipo_anunciante'] === 'loja') {
            $loja = $lojaModel
                ->where('usuario_id', $usuarioId)
                ->where('deleted_at', null)
                ->first();

            if (! $loja) {
                return $this->erro(
                    'loja',
                    'Você precisa possuir uma loja ativa para anunciar como empresa.'
                );
            }

            $lojaId = $loja['id'];
        }

        // 3. Receber as fotos
        $fotos = $this->request->getFileMultiple('fotos') ?? [];

        if (
            count($fotos) < self::MIN_FOTOS ||
            count($fotos) > self::MAX_FOTOS
        ) {
            return $this->erro(
                'fotos',
                'Envie no mínimo 3 e no máximo 5 fotos.'
            );
        }

        foreach ($fotos as $indice => $foto) {
            if (! $foto instanceof UploadedFile || ! $foto->isValid()) {
                return $this->erro(
                    'fotos',
                    'Não foi possível receber a foto ' . ($indice + 1) . '.'
                );
            }

            if ($foto->getSize() > self::MAX_FOTO_BYTES) {
                return $this->erro(
                    'fotos',
                    'Cada foto deve ter no máximo 5 MB.'
                );
            }

            $mimesPermitidos = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];

            if (! in_array($foto->getMimeType(), $mimesPermitidos, true)) {
                return $this->erro(
                    'fotos',
                    'As fotos devem ser JPG, PNG ou WebP.'
                );
            }

            // Verifica também se o conteúdo é uma imagem reconhecida
            if (@getimagesize($foto->getTempName()) === false) {
                return $this->erro(
                    'fotos',
                    'Uma das fotos enviadas não é uma imagem válida.'
                );
            }
        }

        // 4. Receber o vídeo opcional
        $video = $this->request->getFile('video');

        $temVideo = $video !== null &&
            $video->getError() !== UPLOAD_ERR_NO_FILE;

        if ($temVideo) {
            if (! $video->isValid()) {
                return $this->erro(
                    'video',
                    'Não foi possível receber o vídeo.'
                );
            }

            if ($video->getSize() > self::MAX_VIDEO_BYTES) {
                return $this->erro(
                    'video',
                    'O vídeo deve ter no máximo 50 MB.'
                );
            }

            if ($video->getMimeType() !== 'video/mp4') {
                return $this->erro(
                    'video',
                    'O vídeo deve estar no formato MP4.'
                );
            }
        }

        // 5. Preparar armazenamento
        $pastaBase = WRITEPATH . 'uploads/anuncios/';

        if (
            ! is_dir($pastaBase) &&
            ! mkdir($pastaBase, 0750, true) &&
            ! is_dir($pastaBase)
        ) {
            return $this->erro(
                'arquivos',
                'Não foi possível preparar o armazenamento.'
            );
        }

        $pastaAnuncio = null;
        $db = db_connect();

        try {
            $db->transBegin();

            $anuncioModel = new AnuncioModel();
            $midiaModel = new MidiaModel();

            // 6. Salvar anúncio no banco
            $novoAnuncio = [
                'tipo_anunciante' => $validado['tipo_anunciante'],
                'usuario_id' => $usuarioId,
                'loja_id' => $lojaId,
                'cidade_id' => $validado['cidade_id'],
                'contato' => $validado['contato'],
                'data_expiracao' => date(
                    'Y-m-d H:i:s',
                    strtotime('+30 days')
                ),
                'titulo' => $validado['titulo'],
                'ano' => $validado['ano'],
                'preco' => $validado['preco'],
                'cor' => $validado['cor'],
                'descricao' => $validado['descricao'],
                'defeitos' => $validado['defeitos'] ?? null,
                'debitos_pendentes' => $validado['debitos_pendentes'] ?? null,
                'situacao_documentacao' => $validado['situacao_documentacao'],
                'status' => 'ativo',
            ];

            $anuncioId = $anuncioModel->insert($novoAnuncio);

            if (! $anuncioId) {
                throw new RuntimeException(
                    'Falha ao registrar o anúncio.'
                );
            }

            // Pasta exclusiva para o anúncio
            $pastaAnuncio = $pastaBase . $anuncioId . '/';

            if (
                ! is_dir($pastaAnuncio) &&
                ! mkdir($pastaAnuncio, 0750, true)
            ) {
                throw new RuntimeException(
                    'Falha ao criar a pasta do anúncio.'
                );
            }

            // 7. Salvar fotos
            foreach ($fotos as $indice => $foto) {
                $extensoes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];

                $extensao = $extensoes[$foto->getMimeType()];

                $nome = bin2hex(random_bytes(16)) . '.' . $extensao;

                if (! $foto->move($pastaAnuncio, $nome)) {
                    throw new RuntimeException(
                        'Falha ao armazenar uma foto.'
                    );
                }

                $salvou = $midiaModel->insert([
                    'anuncio_id' => $anuncioId,
                    'tipo' => 'foto',
                    'caminho_arquivo' => 'anuncios/' . $anuncioId . '/' . $nome,
                    'ordem' => $indice + 1,
                ]);

                if (! $salvou) {
                    throw new RuntimeException(
                        'Falha ao registrar uma foto.'
                    );
                }
            }

            // 8. Salvar vídeo, se enviado
            if ($temVideo) {
                $nome = bin2hex(random_bytes(16)) . '.mp4';

                if (! $video->move($pastaAnuncio, $nome)) {
                    throw new RuntimeException(
                        'Falha ao armazenar o vídeo.'
                    );
                }

                $salvou = $midiaModel->insert([
                    'anuncio_id' => $anuncioId,
                    'tipo' => 'video',
                    'caminho_arquivo' => 'anuncios/' . $anuncioId . '/' . $nome,
                    'ordem' => 1,
                ]);

                if (! $salvou) {
                    throw new RuntimeException(
                        'Falha ao registrar o vídeo.'
                    );
                }
            }

            // 9. Confirmar tudo
            if ($db->transStatus() === false) {
                throw new RuntimeException(
                    'Falha na transação do banco.'
                );
            }

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Falha ao confirmar o cadastro.'
                );
            }

            return redirect()->to('/')
                ->with('sucesso', 'Anúncio criado com sucesso!');
        } catch (Throwable $e) {
            // Desfaz as alterações no banco
            $db->transRollback();

            // Remove arquivos que já tenham sido enviados
            if ($pastaAnuncio !== null && is_dir($pastaAnuncio)) {
                foreach (glob($pastaAnuncio . '*') ?: [] as $arquivo) {
                    if (is_file($arquivo)) {
                        unlink($arquivo);
                    }
                }

                rmdir($pastaAnuncio);
            }

            log_message(
                'error',
                'Erro ao criar anúncio: ' . $e->getMessage()
            );

            return $this->erro(
                'cadastro',
                'Não foi possível criar o anúncio. Tente novamente.'
            );
        }
    }

    private function erro(
        string $campo,
        string $mensagem
    ): RedirectResponse {
        return redirect()->back()
            ->withInput()
            ->with('erros', [
                $campo => $mensagem,
            ]);
    }
}
