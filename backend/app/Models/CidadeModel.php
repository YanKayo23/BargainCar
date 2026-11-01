<?php

namespace App\Models;

use CodeIgniter\Model;

class CidadeModel extends Model
{
    protected $table = 'cidades';

    protected $primaryKey = 'codigo_ibge';

    protected $returnType = 'array';

    protected $useAutoIncrement = false;

    protected $allowedFields = [
        'codigo_ibge',
        'nome',
        'uf',
        'latitude',
        'longitude',
    ];

    public function buscarPorNome(string $nome, int $limite = 10): array
    {
        return $this
            ->like('nome', $nome, 'after')
            ->orderBy('nome', 'ASC')
            ->limit($limite)
            ->findAll();
    }
}
