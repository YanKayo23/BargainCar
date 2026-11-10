<?php

namespace App\Models;

use CodeIgniter\Model;

class AnuncioModel extends Model
{
    protected $table = 'anuncios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'tipo_anunciante',
        'usuario_id',
        'loja_id',
        'cidade_id',
        'contato',
        'data_expiracao',
        'titulo',
        'ano',
        'preco',
        'cor',
        'descricao',
        'defeitos',
        'debitos_pendentes',
        'situacao_documentacao',
        'status',
        'deleted_at',
    ];

    // Exclusão lógica
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    // Datas gerenciadas pelo banco
    protected $useTimestamps = false;
}
