<?php
/**
 * Variáveis recebidas do PerfilController.
 *
 * @var array $usuario
 * @var string $tempoCadastro
 * @var int $totalAnunciosAtivos
 * @var array|null $lojaAtiva
 * @var array $lojasDesativadas
 * @var array $anunciosPessoais
 */

$nome = $usuario['nome'] ?? 'Usuário';
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));

$temLojaAtiva = ! empty($lojaAtiva);
$totalLojasDesativadas = count($lojasDesativadas ?? []);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil | BargainCar</title>

    <link rel="stylesheet"
        href="<?= base_url('css/MeuPerfil.css') ?>">

    <script src="<?= base_url('js/MeuPerfil.js') ?>" defer></script>
</head>

<body>

    <!-- Navbar compartilhada -->
    <?= view('layouts/Navbar') ?>

    <main class="perfil-container">

        <h1 class="perfil-titulo">Meu perfil</h1>

        <!-- INFORMAÇÕES PESSOAIS -->
        <section class="perfil-card">

            <div class="perfil-identidade">

                <div class="perfil-avatar">
                    <?= esc($inicial) ?>
                </div>

                <div class="perfil-identidade-texto">
                    <h2><?= esc($nome) ?></h2>

                    <p>
                        Na plataforma há
                        <?= esc($tempoCadastro) ?>
                        ·
                        <?= (int) $totalAnunciosAtivos ?>
                        anúncios ativos
                    </p>
                </div>

            </div>

            <a href="#"
                class="perfil-botao-contorno"
                aria-disabled="true"
                onclick="return false;">
                Editar meus dados
            </a>

        </section>

        <div class="perfil-aviso">
            <span aria-hidden="true">ⓘ</span>
            Seu CPF, contato e cidade padrão não aparecem para outras pessoas.
        </div>

        <!-- MINHAS LOJAS -->
        <section class="perfil-secao">

            <div class="perfil-secao-cabecalho">
                <div>
                    <h2>Minhas lojas</h2>
                    <p>Gerencie seus perfis de empresa.</p>
                </div>
            </div>

            <?php if ($temLojaAtiva): ?>

                <div class="perfil-loja-card">

                    <div class="perfil-loja-informacoes">
                        <div class="perfil-loja-icone">L</div>

                        <div>
                            <h3>
                                <?= esc($lojaAtiva['nome_empresa']) ?>
                            </h3>

                            <span class="perfil-status perfil-status-ativa">
                                Ativa
                            </span>
                        </div>
                    </div>

                    <span class="perfil-botao-contorno perfil-desabilitado">
                        Gerenciar
                    </span>

                </div>

            <?php else: ?>

                <div class="perfil-loja-vazia">

                    <div>
                        <h3>
                            <?= $totalLojasDesativadas > 0
                                ? 'Nenhuma loja ativa'
                                : 'Você ainda não tem uma loja' ?>
                        </h3>

                        <p>
                            Crie uma loja para anunciar seus veículos
                            como empresa.
                        </p>
                    </div>

                    <a href="<?= site_url('criar-loja') ?>"
                        class="perfil-botao-azul">
                        <?= $totalLojasDesativadas > 0
                            ? 'Criar nova loja'
                            : 'Criar minha loja' ?>
                    </a>

                </div>

            <?php endif; ?>

            <?php if ($totalLojasDesativadas > 0): ?>

                <details class="perfil-lojas-historico">

                    <summary>
                        Lojas desativadas
                        (<?= $totalLojasDesativadas ?>)
                    </summary>

                    <div class="perfil-historico-conteudo">

                        <?php foreach ($lojasDesativadas as $loja): ?>

                            <div class="perfil-loja-desativada">

                                <div>
                                    <h3>
                                        <?= esc($loja['nome_empresa']) ?>
                                    </h3>

                                    <span class="perfil-status perfil-status-desativada">
                                        Desativada
                                    </span>
                                </div>

                                <?php if (! $temLojaAtiva): ?>

                                    <form
                                        method="post"
                                        class="perfil-form-reativar"
                                        data-nome-loja="<?= esc($loja['nome_empresa'], 'attr') ?>"
                                        action="<?= site_url('lojas/' . $loja['id'] . '/reativar') ?>">
                                    <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                        <p class="perfil-historico-aviso">
                            Ao reativar uma loja, os anúncios antigos
                            permanecem arquivados.
                        </p>

                        <?php if ($temLojaAtiva): ?>
                            <p class="perfil-historico-aviso">
                                Para reativar outra loja, não pode
                                existir uma loja ativa.
                            </p>
                        <?php endif; ?>

                    </div>

                </details>

            <?php endif; ?>

        </section>

        <!-- MEUS ANÚNCIOS PESSOAIS -->
        <section class="perfil-secao">

            <div class="perfil-secao-cabecalho">

                <div>
                    <h2>Meus anúncios</h2>
                    <p>Anúncios publicados como pessoa física.</p>
                </div>

                <a href="<?= site_url('criar-anuncio') ?>"
                    class="perfil-botao-azul">
                    + Novo anúncio
                </a>

            </div>

            <?php if (empty($anunciosPessoais)): ?>

                <div class="perfil-anuncios-vazio">
                    <h3>Você ainda não possui anúncios pessoais.</h3>

                    <p>
                        Publique seu primeiro veículo para começar
                        a anunciar no BargainCar.
                    </p>
                </div>

            <?php else: ?>

                <div class="perfil-anuncios-lista">

                    <?php foreach ($anunciosPessoais as $anuncio): ?>

                        <?php
                        $status = $anuncio['status'] ?? '';

                        if (
                            $status === 'ativo'
                            && ! empty($anuncio['data_expiracao'])
                            && strtotime($anuncio['data_expiracao']) < time()
                        ) {
                            $status = 'expirado';
                        }

                        $rotulos = [
                            'ativo' => 'Ativo',
                            'pendente' => 'Pendente',
                            'expirado' => 'Expirado',
                        ];

                        $statusTexto = $rotulos[$status]
                            ?? ucfirst($status);
                        ?>

                        <article class="perfil-anuncio-card">

                            <div class="perfil-anuncio-imagem">
                                <span>Sem foto</span>
                            </div>

                            <div class="perfil-anuncio-conteudo">

                                <div class="perfil-anuncio-topo">
                                    <h3>
                                        <?= esc($anuncio['titulo']) ?>
                                    </h3>

                                    <span class="perfil-status perfil-status-<?= esc($status, 'attr') ?>">
                                        <?= esc($statusTexto) ?>
                                    </span>
                                </div>

                                <p class="perfil-anuncio-detalhes">
                                    Ano:
                                    <?= esc($anuncio['ano'] ?? 'Não informado') ?>
                                </p>

                                <strong class="perfil-anuncio-preco">
                                    R$
                                    <?= number_format(
                                        (float) ($anuncio['preco'] ?? 0),
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>

                                <div class="perfil-anuncio-acoes">

                                    <a href="<?= site_url(
                                                    'anuncios/' . $anuncio['id']
                                                ) ?>">
                                        Visualizar
                                    </a>

                                    <?php if ($status === 'ativo'): ?>
                                        <span>Editar</span>
                                        <span>Excluir</span>
                                    <?php elseif ($status === 'pendente'): ?>
                                        <span>Renovar</span>
                                        <span>Excluir</span>
                                    <?php else: ?>
                                        <span>Excluir</span>
                                    <?php endif; ?>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>
