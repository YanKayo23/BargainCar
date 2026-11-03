<?php

namespace App\Models;

use CodeIgniter\Model;

class LojaModel extends Model
{
    protected $table = 'lojas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'usuario_id',
        'cidade_id',
        'contato',
        'nome_empresa',
        'cnpj',
    ];

    // Ativa a exclusão lógica
    protected $useSoftDeletes = true;

    // Campo utilizado para registrar a exclusão
    protected $deletedField = 'deleted_at';

    // O banco preenche data_cadastro automaticamente
    protected $useTimestamps = false;

    // Formato de data utilizado no banco
    protected $dateFormat = 'datetime';
}
