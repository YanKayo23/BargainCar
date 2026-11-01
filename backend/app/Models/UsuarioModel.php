<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'nome',
        'email',
        'cpf',
        'cidade_id',
        'contato_padrao',
        'senha_hash',
    ];
}
