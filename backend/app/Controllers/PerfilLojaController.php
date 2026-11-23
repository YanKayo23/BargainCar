<?php

namespace App\Controllers;

use App\Models\AnuncioModel;
use App\Models\CidadeModel;
use App\Models\LojaModel;
use App\Models\MidiaModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class PerfilLojaController extends BaseController
{
    private function lojaAtiva(): ?array
    {
        return (new LojaModel())->where('usuario_id', (int) session()->get('usuario_id'))->first();
    }

    public function index(): string|RedirectResponse
    {
        if (! session()->get('usuario_id')) return redirect()->to(site_url('login'));
        $loja = $this->lojaAtiva();
        if (! $loja) return redirect()->to(site_url('meu-perfil'))->with('erro', 'Você não possui loja ativa.');
        session()->set('modo_perfil', 'loja');
        $cidade = (new CidadeModel())->find($loja['cidade_id']);
        $anuncios = (new AnuncioModel())->withDeleted()->where('loja_id', $loja['id'])
            ->where('tipo_anunciante', 'loja')->orderBy('id', 'DESC')->findAll();
        $ids = array_column($anuncios, 'id');
        $fotos = [];
        if ($ids) {
            foreach ((new MidiaModel())->whereIn('anuncio_id', $ids)->where('tipo', 'foto')
                ->orderBy('ordem', 'ASC')->orderBy('id', 'ASC')->findAll() as $midia) {
                $fotos[$midia['anuncio_id']] ??= $midia['id'];
            }
        }
        foreach ($anuncios as &$anuncio) $anuncio['foto_id'] = $fotos[$anuncio['id']] ?? null;
        unset($anuncio);
        $totais = ['ativos' => 0, 'pendentes' => 0, 'expirados' => 0, 'arquivados' => 0];
        foreach ($anuncios as $anuncio) {
            if (! empty($anuncio['deleted_at'])) {
                $totais['arquivados']++;
            } elseif ($anuncio['status'] === 'expirado' || strtotime((string) $anuncio['data_expiracao']) <= time()) {
                $totais['expirados']++;
            } elseif ($anuncio['status'] === 'pendente') {
                $totais['pendentes']++;
            } else {
                $totais['ativos']++;
            }
        }
        return view('lojas/PerfilLoja', compact('loja', 'cidade', 'anuncios', 'totais'));
    }

    public function sair(): RedirectResponse
    {
        if (! session()->get('usuario_id')) {
            return redirect()->to(site_url('login'));
        }
        session()->set('modo_perfil', 'pessoa');
        return redirect()->to(site_url('meu-perfil'));
    }

    public function editar(): string|RedirectResponse
    {
        if (! session()->get('usuario_id')) {
            return redirect()->to(site_url('login'));
        }
        $loja = $this->lojaAtiva();
        if (! $loja) {
            return redirect()->to(site_url('meu-perfil'))->with('erro', 'Você não possui loja ativa.');
        }
        session()->set('modo_perfil', 'loja');
        $cidade = (new CidadeModel())->where('codigo_ibge', $loja['cidade_id'])->first();
        return view('lojas/EditarLoja', compact('loja', 'cidade'));
    }

    public function atualizar(): RedirectResponse
    {
        if (! session()->get('usuario_id')) {
            return redirect()->to(site_url('login'));
        }
        $loja = $this->lojaAtiva();
        if (! $loja) {
            return redirect()->to(site_url('meu-perfil'))->with('erro', 'Você não possui loja ativa.');
        }

        $dados = [
            'nome_empresa' => trim((string) $this->request->getPost('nome_empresa')),
            'cidade_id' => trim((string) $this->request->getPost('cidade_id')),
            'contato' => trim((string) $this->request->getPost('contato')),
        ];
        $regras = [
            'nome_empresa' => 'required|max_length[150]',
            'cidade_id' => 'required|is_not_unique[cidades.codigo_ibge]',
            'contato' => 'required|max_length[30]',
        ];
        $mensagens = [
            'nome_empresa' => ['required' => 'Informe o nome da empresa.', 'max_length' => 'O nome deve ter até 150 caracteres.'],
            'cidade_id' => ['required' => 'Selecione uma cidade na lista.', 'is_not_unique' => 'Selecione uma cidade válida.'],
            'contato' => ['required' => 'Informe o contato da loja.', 'max_length' => 'O contato deve ter até 30 caracteres.'],
        ];
        if (! $this->validateData($dados, $regras, $mensagens)) {
            return redirect()->to(site_url('perfil-loja/editar'))->withInput()
                ->with('erros', $this->validator->getErrors());
        }
        try {
            $modelo = new LojaModel();
            if ($modelo->update((int) $loja['id'], $this->validator->getValidated()) === false) {
                return redirect()->to(site_url('perfil-loja/editar'))->withInput()
                    ->with('erros', $modelo->errors() ?: ['geral' => 'Não foi possível atualizar a loja.']);
            }
        } catch (Throwable $e) {
            log_message('error', 'Falha ao atualizar dados da loja: ' . $e->getMessage());
            return redirect()->to(site_url('perfil-loja/editar'))->withInput()
                ->with('erro', 'Não foi possível atualizar os dados da loja.');
        }
        return redirect()->to(site_url('perfil-loja'))
            ->with('sucesso', 'Dados da loja atualizados com sucesso!');
    }

    public function imagem(string $tipo): RedirectResponse
    {
        if (! session()->get('usuario_id')) return redirect()->to(site_url('login'));
        $loja = $this->lojaAtiva();
        if (! $loja) return redirect()->to(site_url('meu-perfil'));
        if (! in_array($tipo, ['perfil', 'capa'], true)) {
            return redirect()->to(site_url('perfil-loja/editar'))->with('erro', 'Tipo de imagem inválido.');
        }
        $arquivo = $this->request->getFile('imagem');
        $mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (! $arquivo || ! $arquivo->isValid() || $arquivo->getSize() > 5 * 1024 * 1024
            || ! isset($mimes[$arquivo->getMimeType()])
            || @getimagesize($arquivo->getTempName()) === false) {
            return redirect()->to(site_url('perfil-loja/editar'))
                ->with('erro', 'Envie uma imagem JPG, PNG ou WebP válida de até 5 MB.');
        }
        $pasta = FCPATH . 'uploads/lojas/' . (int) $loja['id'] . '/';
        if (! is_dir($pasta) && ! mkdir($pasta, 0755, true) && ! is_dir($pasta)) {
            return redirect()->to(site_url('perfil-loja/editar'))->with('erro', 'Não foi possível preparar a pasta de imagens.');
        }
        $nome = bin2hex(random_bytes(16)) . '.' . $mimes[$arquivo->getMimeType()];
        $campo = $tipo === 'perfil' ? 'foto_perfil' : 'foto_capa';
        $caminho = 'uploads/lojas/' . (int) $loja['id'] . '/' . $nome;
        try {
            $arquivo->move($pasta, $nome);
            if ((new LojaModel())->update($loja['id'], [$campo => $caminho]) === false) {
                throw new \RuntimeException('Erro ao salvar caminho da imagem.');
            }
            // Apaga somente a imagem anterior pertencente à pasta desta loja.
            $anterior = (string) ($loja[$campo] ?? '');
            $prefixo = 'uploads/lojas/' . (int) $loja['id'] . '/';
            if (str_starts_with($anterior, $prefixo) && basename($anterior) !== '' && is_file(FCPATH . $anterior)) {
                @unlink(FCPATH . $anterior);
            }
        } catch (Throwable $e) {
            @unlink($pasta . $nome);
            log_message('error', 'Erro no upload da loja: ' . $e->getMessage());
            return redirect()->to(site_url('perfil-loja/editar'))->with('erro', 'Não foi possível salvar a imagem.');
        }
        return redirect()->to(site_url('perfil-loja/editar'))->with('sucesso', 'Imagem atualizada com sucesso!');
    }
}
