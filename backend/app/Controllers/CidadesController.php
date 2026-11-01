<?php

namespace App\Controllers;

use App\Models\CidadeModel;
use CodeIgniter\HTTP\ResponseInterface;

class CidadesController extends BaseController
{
    public function index(): ResponseInterface
    {
        // Recebe o texto enviado pela URL
        $busca = trim((string) $this->request->getGet('busca'));

        // Limita o tamanho da pesquisa
        if (mb_strlen($busca) > 100) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'erro' => 'A pesquisa deve ter no máximo 100 caracteres.',
                ]);
        }

        // Evita consultas sem texto
        if (mb_strlen($busca) < 2) {
            return $this->response->setJSON([]);
        }

        // Cria uma instância do Model
        $cidadeModel = new CidadeModel();

        // Pesquisa até 10 cidades
        $cidades = $cidadeModel->buscarPorNome($busca, 10);

        // Retorna o resultado em JSON
        return $this->response->setJSON($cidades);
    }
}
