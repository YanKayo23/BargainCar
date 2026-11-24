<?php
namespace App\Models;
use CodeIgniter\Model;
class PerguntaModel extends Model
{
    protected $table = 'perguntas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['anuncio_id','usuario_id','pergunta','resposta','data_resposta'];
    protected $useTimestamps = false;
}
