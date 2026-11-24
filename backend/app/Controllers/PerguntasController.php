<?php
namespace App\Controllers;
use App\Models\PerguntaModel;
use App\Services\AnuncioCicloVidaService;
class PerguntasController extends BaseController
{
    private function anuncio($db, int $id, bool $lock = false): ?array
    {
        $a = $db->query('SELECT * FROM anuncios WHERE id = ?'.($lock?' FOR UPDATE':''), [$id])->getRowArray();
        if (!$a || !in_array($a['status'], ['ativo','pendente'], true) || AnuncioCicloVidaService::situacao($a) !== null) return null;
        if ($a['tipo_anunciante'] === 'loja') {
            $l = $db->table('lojas')->where('id',$a['loja_id'])->where('usuario_id',$a['usuario_id'])->where('deleted_at',null)->get()->getRowArray();
            if (!$l) return null;
        } elseif ($a['tipo_anunciante'] !== 'pessoa' || $a['loja_id'] !== null) return null;
        return $a;
    }
    private function json(array $data, int $status = 200)
    {
        return $this->response->setHeader('Cache-Control','private, no-store')->setStatusCode($status)->setJSON($data + ['csrfNome'=>csrf_token(),'csrfHash'=>csrf_hash()]);
    }
    public function index(int $id)
    {
        $db = db_connect(); $a = $this->anuncio($db,$id);
        if (!$a) return $this->json(['mensagem'=>'Anúncio indisponível.'],404);
        $uid = (int)session()->get('usuario_id'); $dono = $uid && $uid === (int)$a['usuario_id'];
        $rows = $db->table('perguntas p')->select('p.id,p.usuario_id,p.pergunta,p.resposta,p.data_pergunta,p.data_resposta,u.nome')->join('usuarios u','u.id=p.usuario_id')->where('p.anuncio_id',$id)->orderBy('p.id','DESC')->get()->getResultArray();
        foreach ($rows as &$p) {
            $p['podeExcluir'] = $dono || ($uid && $uid === (int)$p['usuario_id']);
            $p['podeResponder'] = $dono && $p['resposta'] === null;
            unset($p['usuario_id']);
        } unset($p);
        return $this->json(['perguntas'=>$rows,'podePerguntar'=>$uid && !$dono,'logado'=>(bool)$uid,'login'=>site_url('login')]);
    }
    public function criar(int $id) { return $this->alterar($id,0,'criar'); }
    public function responder(int $id, int $pid) { return $this->alterar($id,$pid,'responder'); }
    public function excluir(int $id, int $pid) { return $this->alterar($id,$pid,'excluir'); }
    private function alterar(int $id,int $pid,string $acao)
    {
        $uid = (int)session()->get('usuario_id');
        if (!$uid) return $this->json(['mensagem'=>'Entre na sua conta.'],401);
        $texto = $this->request->getPost('texto');
        if ($acao !== 'excluir' && (!is_string($texto) || trim($texto)==='' || mb_strlen(trim($texto))>500)) return $this->json(['mensagem'=>'Digite de 1 a 500 caracteres.'],422);
        $db = db_connect(); $db->transBegin();
        try {
            $a = $this->anuncio($db,$id,true);
            if (!$a) { $db->transRollback(); return $this->json(['mensagem'=>'Anúncio indisponível.'],404); }
            $dono = $uid === (int)$a['usuario_id'];
            $model = new PerguntaModel($db);
            if ($acao === 'criar') {
                if ($dono) { $db->transRollback(); return $this->json(['mensagem'=>'Não é possível perguntar no próprio anúncio.'],403); }
                $ok = $model->insert(['anuncio_id'=>$id,'usuario_id'=>$uid,'pergunta'=>trim($texto)]);
            } else {
                $p = $db->query('SELECT * FROM perguntas WHERE id=? AND anuncio_id=? FOR UPDATE',[$pid,$id])->getRowArray();
                if (!$p || ($acao==='responder' ? !$dono : (!$dono && $uid !== (int)$p['usuario_id']))) { $db->transRollback(); return $this->json(['mensagem'=>'Operação não autorizada.'],403); }
                if ($acao==='responder' && $p['resposta']!==null) { $db->transRollback(); return $this->json(['mensagem'=>'Pergunta já respondida.'],409); }
                $ok = $acao==='responder' ? $model->update($pid,['resposta'=>trim($texto),'data_resposta'=>date('Y-m-d H:i:s')]) : $model->delete($pid);
            }
            if (!$ok || !$db->transStatus()) throw new \RuntimeException('Falha ao gravar pergunta');
            $db->transCommit();
            return $this->json(['mensagem'=>match($acao){'criar'=>'Pergunta enviada!','responder'=>'Resposta enviada!',default=>'Pergunta excluída.'}]);
        } catch (\Throwable $e) { $db->transRollback(); log_message('error',$e->getMessage()); return $this->json(['mensagem'=>'Não foi possível concluir a operação.'],500); }
    }
}
