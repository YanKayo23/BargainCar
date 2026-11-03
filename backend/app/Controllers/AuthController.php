<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    // Exibe a página de login
    public function login(): string
    {
        return view('auth/login');
    }

    // Recebe os dados do formulário

    public function autenticar(): RedirectResponse
    {
        $cpf = preg_replace(
            '/\D/',
            '',
            (string) $this->request->getPost('cpf')
        );

        $senha = (string) $this->request->getPost('senha');

        if (strlen($cpf) !== 11 || $senha === '') {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'Informe um CPF válido e sua senha.');
        }

        // Configuração do limite de tentativas
        $limite = 5;
        $tempoBloqueio = 60;

        $cache = cache();

        // Identificador do CPF, sem armazená-lo na chave
        $chave = 'login_' . hash('sha256', $cpf);

        $agora = time();

        // Recupera o controle de tentativas
        $controle = $cache->get($chave);

        if (! is_array($controle)) {
            $controle = [
                'tentativas' => 0,
                'bloqueado_ate' => 0,
            ];
        }

        // Verifica se existe bloqueio ativo
        if ($controle['bloqueado_ate'] > $agora) {
            $restante = $controle['bloqueado_ate'] - $agora;

            return redirect()->back()
                ->withInput()
                ->with(
                    'erro',
                    "Muitas tentativas incorretas. Aguarde {$restante} segundos."
                );
        }

        // Reinicia o contador após o bloqueio
        if ($controle['bloqueado_ate'] > 0) {
            $controle = [
                'tentativas' => 0,
                'bloqueado_ate' => 0,
            ];
        }

        // Consulta o usuário
        $usuarioModel = new UsuarioModel();

        $usuario = $usuarioModel
            ->where('cpf', $cpf)
            ->first();

        // Verifica as credenciais
        if (
            ! $usuario ||
            ! password_verify($senha, $usuario['senha_hash'])
        ) {
            $controle['tentativas']++;

            if ($controle['tentativas'] >= $limite) {
                $controle['bloqueado_ate'] = $agora + $tempoBloqueio;

                $mensagem = 'Você atingiu o limite de tentativas. Aguarde 60 segundos.';
            } else {
                $restantes = $limite - $controle['tentativas'];

                $mensagem = "CPF ou senha incorretos. Restam {$restantes} tentativas.";
            }

            // Mantém o controle por 60 segundos
            $cache->save($chave, $controle, $tempoBloqueio);

            return redirect()->back()
                ->withInput()
                ->with('erro', $mensagem);
        }

        // Login correto: remove o contador
        $cache->delete($chave);

        // Renova a sessão
        session()->regenerate(true);

        session()->set([
            'usuario_id' => $usuario['id'],
            'usuario_nome' => $usuario['nome'],
            'logado' => true,
        ]);

        return redirect()->to('/')
            ->with('sucesso', 'Login realizado com sucesso!');
    }



    public function sair(): RedirectResponse
    {
        // Encerra a sessão do usuário
        session()->destroy();

        // Redireciona para a página de login
        return redirect()->to('/login');
    }
}
