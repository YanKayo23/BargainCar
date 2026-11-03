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

    protected $useTimestamps = false;
}
