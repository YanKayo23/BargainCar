<?php

namespace App\Models;

use CodeIgniter\Model;

class MidiaModel extends Model
{
    protected $table = 'midias';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'anuncio_id',
        'tipo',
        'caminho_arquivo',
        'ordem',
    ];

    protected $useTimestamps = false;
}
