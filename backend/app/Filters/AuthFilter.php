<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        // Verifica se o usuário está autenticado
        if (session()->get('logado') !== true) {
            return redirect()->to('/login');
        }

        // Permite continuar a requisição
        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // Nenhuma ação após a resposta
    }
}
