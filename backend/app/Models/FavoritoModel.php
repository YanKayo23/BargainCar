<?php
namespace App\Models;
use CodeIgniter\Model;
class FavoritoModel extends Model
{
    protected $table = 'favoritos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['usuario_id', 'anuncio_id'];
    protected $useTimestamps = false;
}
