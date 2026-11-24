

<?php

/**
 * BargainCar - Meu Perfil
 *
 * Variáveis recebidas do PerfilController.
 *
 * @var array $usuario
 * @var string $tempoCadastro
 * @var int $totalAnunciosAtivos
 * @var array|null $lojaAtiva
 * @var array $lojasDesativadas
 * @var string $nomeCidade
 * @var array $anunciosPessoais
 */

$usuario = $usuario ?? [];
$tempoCadastro = $tempoCadastro ?? '';
$totalAnunciosAtivos = $totalAnunciosAtivos ?? 0;
$lojaAtiva = $lojaAtiva ?? null;
$lojasDesativadas = $lojasDesativadas ?? [];
$anunciosPessoais = $anunciosPessoais ?? [];
$nomeCidade = $nomeCidade ?? 'Não informada';

$nome = $usuario['nome'] ?? 'Usuário';
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));

$temLojaAtiva = ! empty($lojaAtiva);
$totalLojasDesativadas = count($lojasDesativadas);

$mensagemSucesso = session()->getFlashdata('sucesso');
$mensagemErro = session()->getFlashdata('erro');

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Meu Perfil | BargainCar</title>

    <link
        rel="stylesheet"
        href="<?= base_url('css/MeuPerfil.css') ?>"
    >

    <script
        src="<?= base_url('js/MeuPerfil.js') ?>"
        defer
    ></script>
</head>

<body>

    <!-- NAVBAR COMPARTILHADA -->
    <?= view('layouts/Navbar') ?>

    <main class="perfil-container">

        <h1 class="perfil-titulo">Meu perfil</h1>

        <!-- MENSAGENS DE CONFIRMAÇÃO -->

        <?php if ($mensagemSucesso): ?>

            <div
                class="perfil-mensagem-sucesso"
                role="status"
            >
                <?= esc($mensagemSucesso) ?>
            </div>

        <?php endif; ?>

        <?php if ($mensagemErro): ?>

            <div
                class="perfil-mensagem-erro"
                role="alert"
            >
                <?= esc($mensagemErro) ?>
            </div>

        <?php endif; ?>

        <!-- INFORMAÇÕES PESSOAIS -->

        <section class="perfil-card">

            <div class="perfil-identidade">

                <div class="perfil-avatar">
                    <?php if (! empty($usuario['foto_perfil'])): ?>
                        <img src="<?= esc(base_url($usuario['foto_perfil']), 'attr') ?>" alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= esc($inicial) ?>
                    <?php endif; ?>
                </div>

                <div class="perfil-identidade-texto">

                    <h2><?= esc($nome) ?></h2>

                    <p>
                        Na plataforma há
                        <?= esc($tempoCadastro ?? '') ?>
                        ·
                        <?= (int) ($totalAnunciosAtivos ?? 0) ?>
                        anúncios ativos
                    </p>

                </div>

            </div>

            <!-- Encerrar a sessão do usuário -->
            <form action="<?= site_url('logout') ?>" method="post" class="perfil-form-logout">
                <?= csrf_field() ?>
                <button type="submit" class="perfil-botao-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Sair da conta
                </button>
            </form>

        </section>

        <!-- INFORMAÇÕES PRIVADAS: somente o próprio usuário autenticado -->
        <section class="perfil-informacoes">
            <h2>Minhas informações</h2>

            <div class="perfil-informacoes-grid">
                <div class="perfil-informacao">
                    <span>Nome completo</span>
                    <strong><?= esc($usuario['nome'] ?? 'Não informado') ?></strong>
                </div>

                <div class="perfil-informacao">
                    <span>CPF</span>
                    <strong>
                        <?php
                        $cpfNumeros = preg_replace('/\D/', '', (string) ($usuario['cpf'] ?? ''));
                        $cpfFormatado = strlen($cpfNumeros) === 11
                            ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpfNumeros)
                            : 'Não informado';
                        ?>
                        <?= esc($cpfFormatado) ?>
                    </strong>
                </div>

                <div class="perfil-informacao">
                    <span>E-mail</span>
                    <strong><?= esc($usuario['email'] ?? 'Não informado') ?></strong>
                </div>

                <div class="perfil-informacao">
                    <span>Cidade cadastrada</span>
                    <strong><?= esc($nomeCidade) ?></strong>
                </div>

                <div class="perfil-informacao">
                    <span>Contato padrão</span>
                    <strong><?= esc($usuario['contato_padrao'] ?: 'Não informado') ?></strong>
                </div>

                <div class="perfil-informacao">
                    <span>Senha</span>
                    <strong>••••••••</strong>
                </div>
            </div>

            <div class="perfil-informacoes-acoes">
                <a href="<?= site_url('editar-perfil') ?>" class="perfil-botao-contorno">
                    Editar meus dados
                </a>
            </div>
        </section>

        <div class="perfil-aviso">

            <span aria-hidden="true">ⓘ</span>

            Seu CPF, contato e cidade padrão não
            aparecem para outras pessoas.

        </div>

        <!-- MINHAS LOJAS -->

        <section class="perfil-secao">

            <div class="perfil-secao-cabecalho">

                <div>
                    <h2>Minhas lojas</h2>

                    <p>
                        Gerencie seus perfis de empresa.
                    </p>
                </div>

            </div>

            <?php if ($temLojaAtiva): ?>

                <div class="perfil-loja-card">

                    <div class="perfil-loja-informacoes">

                        <div class="perfil-loja-icone">
                            L
                        </div>

                        <div>

                            <h3>
                                <?= esc($lojaAtiva['nome_empresa']) ?>
                            </h3>

                            <span
                                class="perfil-status perfil-status-ativa"
                            >
                                Ativa
                            </span>

                        </div>

                    </div>

                    <a href="<?= site_url('perfil-loja') ?>" class="perfil-botao-contorno">Gerenciar</a>

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
                            Crie uma loja para anunciar seus
                            veículos como empresa.
                        </p>

                    </div>

                    <a
                        href="<?= site_url('criar-loja') ?>"
                        class="perfil-botao-azul"
                    >
                        <?= $totalLojasDesativadas > 0
                            ? 'Criar nova loja'
                            : 'Criar minha loja' ?>
                    </a>

                </div>

            <?php endif; ?>

            <!-- LOJAS DESATIVADAS -->

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

                                    <span
                                        class="perfil-status perfil-status-desativada"
                                    >
                                        Desativada
                                    </span>

                                </div>

                                <?php if (! $temLojaAtiva): ?>

                                    <form
                                        method="post"
                                        class="perfil-form-reativar"
                                        data-nome-loja="<?= esc($loja['nome_empresa'], 'attr') ?>"
                                        action="<?= site_url('lojas/' . $loja['id'] . '/reativar') ?>"
                                    >

                                        <?= csrf_field() ?>

                                        <button
                                            type="submit"
                                            class="perfil-botao-contorno"
                                        >
                                            Reativar
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                        <p class="perfil-historico-aviso">
                            Ao reativar uma loja, os anúncios
                            antigos permanecem arquivados.
                        </p>

                        <?php if ($temLojaAtiva): ?>

                            <p class="perfil-historico-aviso">
                                Para reativar outra loja,
                                não pode existir uma loja ativa.
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

                    <p>
                        Anúncios publicados como pessoa física.
                    </p>

                </div>

                <a
                    href="<?= site_url('criar-anuncio') ?>"
                    class="perfil-botao-azul"
                >
                    + Novo anúncio
                </a>

            </div>

            <?php if (empty($anunciosPessoais)): ?>

                <div class="perfil-anuncios-vazio">

                    <h3>
                        Você ainda não possui anúncios pessoais.
                    </h3>

                    <p>
                        Publique seu primeiro veículo para
                        começar a anunciar no BargainCar.
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
                            && strtotime($anuncio['data_expiracao']) <= time()
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

                            <!-- IMAGEM DO ANÚNCIO -->

                            <div class="perfil-anuncio-imagem">

                                <?php if (! empty($anuncio['primeira_foto'])): ?>

                                    <img
                                        src="<?= site_url(
                                            'anuncios/'
                                            . $anuncio['id']
                                            . '/midias/'
                                            . $anuncio['primeira_foto']['id']
                                        ) ?>"
                                        alt="Foto de <?= esc($anuncio['titulo'], 'attr') ?>"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <span>Sem foto</span>

                                <?php endif; ?>

                            </div>

                            <!-- DADOS DO ANÚNCIO -->

                            <div class="perfil-anuncio-conteudo">

                                <div class="perfil-anuncio-topo">

                                    <h3>
                                        <?= esc($anuncio['titulo']) ?>
                                    </h3>

                                    <span
                                        class="perfil-status perfil-status-<?= esc($status, 'attr') ?>"
                                    >
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

                                <!-- AÇÕES DO ANÚNCIO -->

                                <div class="perfil-anuncio-acoes">

                                    <?php if ($status === 'ativo'): ?>

                                        <a
                                            href="<?= site_url('anuncios/' . $anuncio['id']) ?>"
                                        >
                                            Visualizar
                                        </a>

                                    <?php endif; ?>

                                    <a
                                        href="<?= site_url(
                                            'anuncios/'
                                            . $anuncio['id']
                                            . '/editar?origem=perfil'
                                        ) ?>"
                                    >
                                        Editar
                                    </a>

                                    <?php if ($status === 'pendente'): ?>

                                        <span>Renovar</span>

                                    <?php endif; ?>

                                    <!-- EXCLUSÃO LÓGICA -->

                                    <form
                                        method="post"
                                        class="perfil-form-excluir"
                                        data-titulo-anuncio="<?= esc($anuncio['titulo'], 'attr') ?>"
                                        action="<?= site_url(
                                            'anuncios/'
                                            . $anuncio['id']
                                            . '/excluir'
                                        ) ?>"
                                    >

                                        <?= csrf_field() ?>

                                        <button
                                            type="submit"
                                            class="perfil-acao-excluir"
                                        >
                                            Excluir
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

<section class="perfil-secao"><h2>Histórico de anúncios</h2><p>Somente você pode visualizar este histórico.</p>
<?php if (empty($historico)): ?><p>Nenhum anúncio no histórico.</p><?php endif; ?>
<div class="pl-historico-lista"><?php foreach ($historico ?? [] as $item): ?>
<?php $situacao = $item['situacao_historico']; $acao = \App\Services\AnuncioCicloVidaService::acao($item); $data = $situacao === 'expirado' ? $item['data_expiracao'] : $item['deleted_at']; ?>
<article class="pl-historico-item" data-situacao="<?= esc($situacao,'attr') ?>">
<div class="pl-historico-foto"><?php if ($item['foto_id']): ?><img loading="lazy" src="<?= site_url('anuncios/'.$item['id'].'/midias-historico/'.$item['foto_id']) ?>" alt="Foto do veículo"><?php else: ?>Sem foto<?php endif; ?></div>
<div class="pl-historico-dados"><h3><?= esc($item['titulo']) ?></h3><strong>R$ <?= number_format((float)$item['preco'],2,',','.') ?></strong>
<p><?= esc(['excluido'=>'Excluído','expirado'=>'Expirado','moderacao'=>'Removido por moderação'][$situacao]) ?> · <?= $data ? esc(date('d/m/Y',strtotime($data))) : 'Data não informada' ?></p>
<?php if ($acao === 'restaurar' && !($somenteConsulta ?? false)): ?>
<form method="post" action="<?= site_url('anuncios/'.$item['id'].'/restaurar') ?>" class="form-restaurar-anuncio"><?= csrf_field() ?><input type="hidden" name="confirmar" value="1"> <button class="pl-btn pl-contorno" type="submit">Restaurar</button></form>
<?php elseif ($acao === 'republicar' && !($somenteConsulta ?? false)): ?>
<a class="pl-btn pl-contorno" href="<?= site_url('anuncios/'.$item['id'].'/republicar') ?>">Republicar</a>
<?php else: ?><span>Operação indisponível</span><?php endif; ?>
</div></article>
<?php endforeach; ?></div></section>
    </main>

    <!-- MODAL DE CONFIRMAÇÃO DA EXCLUSÃO -->

    <dialog
        id="modal-excluir-anuncio"
        class="perfil-modal"
        aria-labelledby="modal-excluir-titulo"
        aria-describedby="modal-excluir-texto"
    >

        <h2 id="modal-excluir-titulo">
            Excluir anúncio?
        </h2>

        <p id="modal-excluir-texto">
            Tem certeza de que deseja excluir este anúncio?
        </p>

        <p class="perfil-modal-aviso">
            O anúncio deixará de aparecer publicamente.
        </p>

        <div class="perfil-modal-acoes">

            <button
                type="button"
                id="modal-excluir-cancelar"
                class="perfil-modal-cancelar"
            >
                Cancelar
            </button>

            <button
                type="button"
                id="modal-excluir-confirmar"
                class="perfil-modal-confirmar"
            >
                Sim, excluir
            </button>

        </div>

    </dialog>

<dialog id="modal-restaurar-anuncio" class="perfil-modal-restaurar" aria-labelledby="titulo-restaurar">
    <div class="restaurar-icone" aria-hidden="true">↶</div>
    <h2 id="titulo-restaurar">Restaurar anúncio</h2>
    <p>Tem certeza de que deseja restaurar este anúncio?</p>
    <p class="restaurar-aviso">O anúncio voltará a ficar disponível, mantendo a validade original, as mídias, os favoritos e as perguntas.</p>
    <div class="restaurar-acoes">
        <button type="button" id="restaurar-cancelar" autofocus>Cancelar</button>
        <button type="button" id="restaurar-confirmar">Sim, restaurar</button>
    </div>
</dialog>
</body>

</html>
