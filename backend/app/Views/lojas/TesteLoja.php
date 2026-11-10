
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de loja - BargainCar</title>
</head>
<body>

    <h1>Teste de desativação e reativação</h1>

    <?php if (session()->getFlashdata('sucesso')): ?>
        <p style="color: green;">
            <?= esc(session()->getFlashdata('sucesso')) ?>
        </p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('erro')): ?>
        <p style="color: red;">
            <?= esc(session()->getFlashdata('erro')) ?>
        </p>
    <?php endif; ?>

    <h2>Loja Teste (ID 1)</h2>

    <form action="<?= site_url('lojas/1/desativar') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit">Desativar loja</button>
    </form>

    <br>

    <form action="<?= site_url('lojas/1/reativar') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit">Reativar loja</button>
    </form>

</body>
</html>
