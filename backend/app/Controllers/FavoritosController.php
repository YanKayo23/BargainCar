<?php
namespace App\Controllers;
use App\Models\FavoritoModel;
use App\Services\AnuncioCicloVidaService;
class FavoritosController extends BaseController
{
    private function publicavel(array $a): bool
    {
        return in_array($a['status'], ['ativo','pendente'], true)
            && AnuncioCicloVidaService::situacao($a) === null
            && ($a['tipo_anunciante'] !== 'loja' || (!empty($a['loja_ativa']) && (int)$a['loja_dono'] === (int)$a['usuario_id']));
    }
    private function consulta($db)
    {
        return $db->table('anuncios a')->select('a.id,a.titulo,a.preco,a.ano,a.cor,a.status,a.data_expiracao,a.deleted_at,a.motivo_indisponibilidade,a.tipo_anunciante,a.usuario_id,l.id AS loja_ativa,l.usuario_id AS loja_dono,c.nome AS cidade_nome,c.uf AS cidade_uf')
            ->join('lojas l','l.id = a.loja_id AND l.deleted_at IS NULL','left')
            ->join('cidades c','c.codigo_ibge = a.cidade_id','left');
    }
    public function index()
    {
        $uid = (int)session()->get('usuario_id');
        if (!$uid) return redirect()->to(site_url('login'));
        $db = db_connect();
        $rows = $this->consulta($db)->join('favoritos f','f.anuncio_id = a.id')->where('f.usuario_id',$uid)->orderBy('f.id','DESC')->get()->getResultArray();
        $favoritos = [];
        foreach ($rows as $a) {
            if (!$this->publicavel($a)) {
                $favoritos[] = ['id'=>$a['id'],'disponivel'=>false];
                continue;
            }
            $foto = $db->table('midias')->select('id')->where('anuncio_id',$a['id'])->where('tipo','foto')->orderBy('ordem')->orderBy('id')->get(1)->getRowArray();
            $favoritos[] = ['id'=>$a['id'],'disponivel'=>true,'titulo'=>$a['titulo'],'preco'=>$a['preco'],'ano'=>$a['ano'],'cor'=>$a['cor'],'tipo_anunciante'=>$a['tipo_anunciante'],'cidade_nome'=>$a['cidade_nome'],'cidade_uf'=>$a['cidade_uf'],'foto_id'=>$foto['id'] ?? null];
        }
        $this->response->setHeader('Cache-Control','private, no-store');
        return view('usuarios/Favoritos',compact('favoritos'));
    }
    private function json(array $dados, int $status = 200)
    {
        return $this->response->setHeader('Cache-Control','private, no-store')->setStatusCode($status)->setJSON($dados + ['csrfNome'=>csrf_token(),'csrfHash'=>csrf_hash()]);
    }
    public function estado()
    {
        $uid = (int)session()->get('usuario_id');
        $ids = $uid ? array_map('intval',array_column((new FavoritoModel())->select('anuncio_id')->where('usuario_id',$uid)->findAll(),'anuncio_id')) : [];
        return $this->json(['logado'=>(bool)$uid,'ids'=>$ids,'login'=>site_url('login')]);
    }
    public function alternar(int $id)
    {
        $uid = (int)session()->get('usuario_id');
        if (!$uid) return $this->json(['mensagem'=>'Entre na sua conta.','login'=>site_url('login')],401);
        $db = db_connect();
        $db->transBegin();
        try {
            // Serializa alterações da mesma conta e revalida o anúncio.
            $db->query('SELECT id FROM usuarios WHERE id = ? FOR UPDATE',[$uid]);
            $db->query('SELECT id FROM anuncios WHERE id = ? FOR UPDATE',[$id]);
            $f = $db->table('favoritos')->where('usuario_id',$uid)->where('anuncio_id',$id);
            $existe = $f->get()->getRowArray();
            if ($existe) {
                $db->table('favoritos')->where('usuario_id',$uid)->where('anuncio_id',$id)->delete();
                $ativo = false;
            } else {
                $a = $this->consulta($db)->where('a.id',$id)->get()->getRowArray();
                if (!$a || !$this->publicavel($a)) {
                    $db->transRollback();
                    return $this->json(['mensagem'=>'Este anúncio não está mais disponível.'],409);
                }
                $db->table('favoritos')->insert(['usuario_id'=>$uid,'anuncio_id'=>$id]);
                $ativo = true;
            }
            if (!$db->transStatus()) throw new \RuntimeException('Falha na transação');
            $db->transCommit();
            return $this->json(['favoritado'=>$ativo,'mensagem'=>$ativo?'Anúncio adicionado aos favoritos!':'Anúncio removido dos favoritos.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error','Falha em favoritos: {erro}',['erro'=>$e->getMessage()]);
            return $this->json(['mensagem'=>'Não foi possível atualizar os favoritos.'],500);
        }
    }
}
