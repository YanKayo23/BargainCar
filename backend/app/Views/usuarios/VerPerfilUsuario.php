<?php
$nome = $usuario['nome'];
$inicial = mb_strtoupper(mb_substr($nome, 0, 1));
$cadastro = !empty($usuario['data_cadastro']) ? new DateTimeImmutable($usuario['data_cadastro']) : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($nome) ?> | BargainCar</title>
    <link rel="stylesheet" href="<?= base_url('css/VerPerfilUsuario.css') ?>">
    <script src="<?= base_url('js/VerPerfilUsuario.js') ?>" defer></script>
</head>
<body>
<?= view('layouts/Navbar') ?>
<main class="vu-container">
    <a class="vu-voltar" href="<?= site_url('/') ?>">← Voltar à Home</a>
    <section class="vu-painel vu-identidade">
        <div class="vu-avatar">
            <?php if (!empty($usuario['foto_perfil'])): ?>
                <img src="<?= esc(base_url($usuario['foto_perfil']), 'attr') ?>" alt="Foto de perfil">
            <?php else: ?><?= esc($inicial) ?><?php endif; ?>
        </div>
        <div class="vu-dados">
            <h1><?= esc($nome) ?></h1>
            <p>Pessoa física</p>
            <p><?= $cadastro ? 'Na plataforma desde ' . esc($cadastro->format('m/Y')) : 'Usuário BargainCar' ?></p>
        </div>
        <div class="vu-total"><strong><?= count($anuncios) ?></strong><span>Anúncios disponíveis</span></div>
    </section>
    <section class="vu-painel vu-anuncios">
        <h2>Anúncios do usuário</h2>
        <p>Confira os veículos anunciados por este usuário.</p>
        <?php if (!$anuncios): ?>
            <p class="vu-vazio">Este usuário não possui anúncios disponíveis no momento.</p>
        <?php else: ?>
            <div class="vu-topo"><label for="vu-ordem">Ordenar por</label>
                <select id="vu-ordem"><option value="recentes">Mais recentes</option><option value="menor">Menor preço</option><option value="maior">Maior preço</option></select>
            </div>
            <div class="vu-grid" id="vu-grid">
                <?php foreach ($anuncios as $a): ?>
                    <article class="vu-card" data-id="<?= (int)$a['id'] ?>" data-preco="<?= esc((string)$a['preco'], 'attr') ?>">
                        <div class="vu-foto">
                            <?php if (isset($fotos[$a['id']])): ?>
                                <img loading="lazy" src="<?= site_url('anuncios/' . $a['id'] . '/midias/' . $fotos[$a['id']]) ?>" alt="<?= esc($a['titulo'], 'attr') ?>">
                            <?php else: ?><span>Sem foto disponível</span><?php endif; ?>
                        </div>
                        <div class="vu-card-info">
                            <h3><?= esc($a['titulo']) ?></h3>
                            <strong>R$ <?= number_format((float)$a['preco'], 2, ',', '.') ?></strong>
                            <p><?= esc($a['ano']) ?> · <?= esc($a['cor']) ?></p>
                            <a class="vu-ver" href="<?= site_url('anuncios/' . $a['id']) ?>">Ver anúncio</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
