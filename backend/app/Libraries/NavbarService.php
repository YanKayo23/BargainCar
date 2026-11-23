<?php

namespace App\Libraries;

use App\Models\UsuarioModel;
use App\Models\LojaModel;

class NavbarService
{
    public function obterDados(): array
    {
        // 1. Identifica o usuário pela sessão.
        $usuarioId = session()->get('usuario_id');

        // Dados padrão para visitantes.
        $dados = [
            'logado' => false,
            'usuario' => null,
            'lojaAtiva' => null,
            'lojasDesativadas' => [],
            'modoPerfil' => 'pessoa',
        ];

        // Se não houver usuário autenticado,
        // retorna os dados padrão.
        if (! $usuarioId) {
            return $dados;
        }

        // 2. Consulta o usuário no banco.
        $usuarioModel = new UsuarioModel();

        $usuario = $usuarioModel->find($usuarioId);

        if (! $usuario) {
            return $dados;
        }

        // 3. Consulta as lojas do usuário.
        $lojaModel = new LojaModel();

        // O soft delete faz esta consulta
        // ignorar lojas desativadas.
        $lojaAtiva = $lojaModel
            ->where('usuario_id', $usuarioId)
            ->first();

        // Busca somente as lojas desativadas.
        $lojasDesativadas = $lojaModel
            ->onlyDeleted()
            ->where('usuario_id', $usuarioId)
            ->orderBy('id', 'DESC')
            ->findAll();

        // Somente uma loja ativa permite navegar em modo loja.
        $modoPerfil = ($lojaAtiva && session()->get('modo_perfil') === 'loja')
            ? 'loja' : 'pessoa';

        // 4. Retorna os dados para a navbar.
        return [
            'logado' => true,
            'modoPerfil' => $modoPerfil,

            // Enviamos somente as informações
            // necessárias para a navegação.
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'cidade_id' => $usuario['cidade_id'],
            ],

            'lojaAtiva' => $lojaAtiva,

            'lojasDesativadas' => $lojasDesativadas,
        ];
    }
}
