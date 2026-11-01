
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pesquisar cidades - BargainCar</title>

    <link rel="stylesheet"
          href="<?= base_url('css/cidades.css') ?>">
</head>
<body>
    <div class="container">
        <h2>Pesquisar cidade</h2>

        <input
            type="text"
            id="busca"
            placeholder="Digite o nome da cidade"
            autocomplete="off"
        >

        <div id="resultados"></div>
        <p id="selecionada"></p>
    </div>

    <script src="<?= base_url('js/cidades.js') ?>"></script>
</body>
</html>
